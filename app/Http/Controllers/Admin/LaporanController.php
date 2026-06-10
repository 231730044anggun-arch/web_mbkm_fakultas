<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    private array $magangStatuses = ['berjalan', 'selesai'];

    public function index(Request $request)
    {
        $monitoringRows = $this->monitoringQuery($request)
            ->latest('updated_at')
            ->get();

        $pengajuan_selesai = $monitoringRows->filter(fn($row) => $this->finalStatus($row) === 'selesai')->count();
        $pengajuan_proses_penilaian = $monitoringRows->filter(fn($row) => $this->finalStatus($row) === 'proses_penilaian')->count();
        $pengajuan_berjalan = $monitoringRows->filter(fn($row) => $this->finalStatus($row) === 'berjalan')->count();
        $total_mitra_aktif = $monitoringRows->pluck('mitra_id')->filter()->unique()->count();
        $kota_penempatan = $monitoringRows
            ->map(fn($row) => $this->displayKota($row))
            ->filter(fn($kota) => $kota !== 'Tidak diketahui')
            ->unique(fn($kota) => Str::lower($kota))
            ->count();

        $topKota = $monitoringRows
            ->groupBy(fn($row) => $this->displayKota($row))
            ->map(function ($rows, $kota) {
                $topMitras = $rows->groupBy(fn($row) => $row->mitra->nama_instansi ?? $row->nama_instansi_manual ?? 'Tidak diketahui')
                    ->map->count()
                    ->sortDesc()
                    ->take(3);

                return (object) [
                    'kota' => $kota,
                    'total' => $rows->count(),
                    'top_mitras' => $topMitras,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $topMitras = $monitoringRows
            ->groupBy(fn($row) => $row->mitra->nama_instansi ?? $row->nama_instansi_manual ?? 'Tidak diketahui')
            ->map(fn($rows, $nama) => (object) [
                'nama' => $nama,
                'kota' => $this->displayKota($rows->first()),
                'total' => $rows->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $mapPoints = $this->mapPoints($topKota);
        $maxKota = max((int) ($topKota->max('total') ?? 0), 1);
        $maxMitra = max((int) ($topMitras->max('total') ?? 0), 1);

        $periodes = Periode::orderByDesc('id')->get();
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $mitras = Mitra::orderBy('nama_instansi')->get();
        $kotaOptions = Mitra::query()
            ->whereNotNull('kota')
            ->where('kota', '<>', '')
            ->orderBy('kota')
            ->pluck('kota')
            ->unique()
            ->values();

        return view('admin.laporan.index', compact(
            'monitoringRows',
            'pengajuan_berjalan',
            'pengajuan_proses_penilaian',
            'pengajuan_selesai',
            'total_mitra_aktif',
            'kota_penempatan',
            'topKota',
            'topMitras',
            'mapPoints',
            'maxKota',
            'maxMitra',
            'periodes',
            'prodis',
            'mitras',
            'kotaOptions'
        ));
    }

    public function export(Request $request)
    {
        $rows = $this->monitoringQuery($request)
            ->latest('updated_at')
            ->get();

        $filename = 'monitoring-magang-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Mahasiswa', 'NIM', 'Program Studi', 'Mitra', 'Kota', 'Status Magang', 'Dosen Pembimbing', 'Periode']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->mahasiswa->nama_lengkap ?? '-',
                    $row->mahasiswa->nim ?? '-',
                    $row->mahasiswa->prodi->nama_prodi ?? '-',
                    $row->mitra->nama_instansi ?? $row->nama_instansi_manual ?? '-',
                    $this->displayKota($row),
                    match ($this->finalStatus($row)) {
                        'selesai' => 'Selesai',
                        'proses_penilaian' => 'Proses Penilaian',
                        default => 'Berjalan',
                    },
                    $row->bimbingans->pluck('dosen.nama_dosen')->filter()->unique()->implode(', ') ?: '-',
                    $row->periode->nama_periode ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function monitoringQuery(Request $request)
    {
        return PengajuanMagang::query()
            ->with(['mahasiswa.prodi', 'mitra', 'periode', 'bimbingans.dosen', 'penilaian'])
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', $this->magangStatuses)
            ->when($request->filled('periode_id'), fn($query) => $query->where('periode_id', $request->periode_id))
            ->when($request->filled('mitra_id'), fn($query) => $query->where('mitra_id', $request->mitra_id))
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'selesai') {
                    return $query->whereHas('penilaian', fn($subQuery) => $subQuery->whereNotNull('nilai_akhir'));
                }

                if ($request->status === 'proses_penilaian') {
                    return $query->whereIn('status_seminar', ['pending', 'menunggu', 'terjadwal', 'selesai'])
                        ->where(function ($statusQuery) {
                            $statusQuery->whereDoesntHave('penilaian')
                                ->orWhereHas('penilaian', fn($subQuery) => $subQuery->whereNull('nilai_akhir'));
                        });
                }

                return $query->where('status_pengajuan', 'berjalan')
                    ->where(function ($statusQuery) {
                        $statusQuery->whereNotIn('status_seminar', ['pending', 'menunggu', 'terjadwal', 'selesai'])
                            ->orWhereNull('status_seminar')
                            ->orWhere('status_seminar', 'belum');
                    });
            })
            ->when($request->filled('prodi_id'), fn($query) => $query->whereHas('mahasiswa', fn($subQuery) => $subQuery->where('prodi_id', $request->prodi_id)))
            ->when($request->filled('kota'), fn($query) => $query->whereHas('mitra', fn($subQuery) => $subQuery->where('kota', $request->kota)));
    }

    private function finalStatus(PengajuanMagang $pengajuan): string
    {
        if ($pengajuan->penilaian?->nilai_akhir !== null) {
            return 'selesai';
        }

        if ($pengajuan->hasValidSeminar()) {
            return 'proses_penilaian';
        }

        return 'berjalan';
    }

    private function displayKota(PengajuanMagang $pengajuan): string
    {
        return filled($pengajuan->mitra?->kota) ? $pengajuan->mitra->kota : 'Tidak diketahui';
    }

    private function mapPoints($topKota)
    {
        $coordinates = [
            'aceh' => [5.55, 95.32], 'banda aceh' => [5.55, 95.32],
            'medan' => [3.59, 98.67], 'padang' => [-0.95, 100.35], 'pekanbaru' => [0.51, 101.45],
            'palembang' => [-2.99, 104.76], 'lampung' => [-5.43, 105.27], 'bandar lampung' => [-5.43, 105.27],
            'jakarta' => [-6.20, 106.82], 'bogor' => [-6.60, 106.80], 'depok' => [-6.40, 106.82],
            'tangerang' => [-6.18, 106.63], 'bekasi' => [-6.24, 106.99], 'serang' => [-6.12, 106.15],
            'cilegon' => [-6.00, 106.05], 'bandung' => [-6.91, 107.61], 'semarang' => [-6.97, 110.42],
            'yogyakarta' => [-7.80, 110.37], 'surakarta' => [-7.57, 110.82], 'solo' => [-7.57, 110.82],
            'surabaya' => [-7.25, 112.75], 'malang' => [-7.98, 112.63], 'denpasar' => [-8.65, 115.22],
            'mataram' => [-8.58, 116.12], 'pontianak' => [-0.03, 109.34], 'banjarmasin' => [-3.32, 114.59],
            'balikpapan' => [-1.27, 116.83], 'samarinda' => [-0.50, 117.15], 'makassar' => [-5.14, 119.43],
            'manado' => [1.47, 124.84], 'kendari' => [-3.99, 122.51], 'ambon' => [-3.69, 128.18],
            'jayapura' => [-2.53, 140.72],
        ];

        return $topKota->map(function ($row) use ($coordinates) {
            $key = Str::lower(trim($row->kota));
            $coordinate = $coordinates[$key] ?? null;

            return (object) [
                'kota' => $row->kota,
                'total' => $row->total,
                'x' => $coordinate ? round((($coordinate[1] - 95) / (141 - 95)) * 100, 2) : null,
                'y' => $coordinate ? round(((6 - $coordinate[0]) / (6 - (-11))) * 100, 2) : null,
                'has_coordinate' => (bool) $coordinate,
            ];
        })->filter(fn($row) => $row->has_coordinate)->values();
    }
}
