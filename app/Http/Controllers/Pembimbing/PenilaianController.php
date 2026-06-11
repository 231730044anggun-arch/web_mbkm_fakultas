<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PenilaianController extends Controller
{
    public function index()
    {
        $pengajuans = $this->baseQuery()
            ->with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian', 'absensis'])
            ->latest('updated_at')
            ->paginate(15);

        return view('pembimbing.penilaian.index', compact('pengajuans'));
    }

    public function create($pengajuanId)
    {
        $pengajuan = $this->findPengajuan($pengajuanId);
        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $rekapAbsensi = $this->rekapAbsensi($pengajuan);
        $canInput = $pengajuan->hasValidSeminar();

        return view('pembimbing.penilaian.create', compact('pengajuan', 'penilaian', 'rekapAbsensi', 'canInput'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuan($pengajuanId);

        if (!$pengajuan->hasValidSeminar()) {
            return redirect()->route('pembimbing.penilaian.create', $pengajuanId)
                ->with('error', 'Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.');
        }

        $request->validate($this->rules());

        $penilaian = Penilaian::updateOrCreate(
            ['pengajuan_id' => $pengajuanId],
            [
                'pembimbing_kehadiran_disiplin' => $request->pembimbing_kehadiran_disiplin,
                'pembimbing_kinerja_sikap' => $request->pembimbing_kinerja_sikap,
                'pembimbing_logbook_kegiatan' => $request->pembimbing_logbook_kegiatan,
                'pembimbing_luaran' => $request->pembimbing_luaran,
                'pembimbing_laporan_akhir' => $request->pembimbing_laporan_akhir,
                'catatan_mitra' => $request->catatan_mitra,
                'catatan' => $request->catatan_mitra,
            ]
        );

        $penilaian->calculateFinalScore();

        if ($penilaian->nilai_akhir !== null && $pengajuan->status_pengajuan !== 'selesai') {
            $pengajuan->update(['status_pengajuan' => 'selesai']);
        }

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Nilai Pembimbing Lapangan Tersimpan',
                'pesan' => 'Nilai dari pembimbing lapangan telah diperbarui. Nilai akhir tampil setelah nilai dosen pembimbing juga lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('pembimbing.penilaian.index')->with('success', 'Nilai pembimbing lapangan berhasil disimpan.');
    }

    private function rules(): array
    {
        return [
            'pembimbing_kehadiran_disiplin' => 'required|numeric|min:0|max:100',
            'pembimbing_kinerja_sikap' => 'required|numeric|min:0|max:100',
            'pembimbing_logbook_kegiatan' => 'required|numeric|min:0|max:100',
            'pembimbing_luaran' => 'required|numeric|min:0|max:100',
            'pembimbing_laporan_akhir' => 'required|numeric|min:0|max:100',
            'catatan_mitra' => 'nullable|string|max:1000',
        ];
    }

    private function baseQuery()
    {
        return PengajuanMagang::query()
            ->where('pembimbing_lapangan_id', auth()->user()->pembimbingLapangan?->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);
    }

    private function findPengajuan($pengajuanId): PengajuanMagang
    {
        return $this->baseQuery()
            ->with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian', 'absensis'])
            ->findOrFail($pengajuanId);
    }

    private function rekapAbsensi(PengajuanMagang $pengajuan): array
    {
        $total = count($this->workdays($pengajuan));
        $absensis = $pengajuan->absensis;
        $approved = $absensis->where('status', 'disetujui')->count();

        return [
            'total_hari_wajib' => $total,
            'jumlah_absensi_masuk' => $absensis->count(),
            'jumlah_disetujui' => $approved,
            'jumlah_pending' => $absensis->where('status', 'pending')->count(),
            'jumlah_revisi' => $absensis->whereIn('status', ['revisi', 'ditolak'])->count(),
            'persentase_kehadiran' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ];
    }

    private function workdays(PengajuanMagang $pengajuan): array
    {
        if (!$pengajuan->tanggal_mulai || !$pengajuan->tanggal_selesai) return [];
        $dates = [];
        $start = Carbon::parse($pengajuan->tanggal_mulai);
        $end = Carbon::parse($pengajuan->tanggal_selesai);
        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            if ($date->isWeekday()) $dates[] = $date->toDateString();
        }
        return $dates;
    }
}