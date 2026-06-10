<?php
namespace App\Http\Controllers\Mitra;

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
        $pengajuans = $this->baseQueryForMitra()
            ->with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian', 'absensis'])
            ->latest('updated_at')
            ->paginate(15);

        return view('mitra.penilaian.index', compact('pengajuans'));
    }

    public function create($pengajuanId)
    {
        $pengajuan = $this->findPengajuanForMitra($pengajuanId);
        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $rekapAbsensi = $this->rekapAbsensi($pengajuan);
        $canInput = $pengajuan->hasValidSeminar();

        return view('mitra.penilaian.create', compact('pengajuan', 'penilaian', 'rekapAbsensi', 'canInput'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForMitra($pengajuanId);

        if (!$pengajuan->hasValidSeminar()) {
            return redirect()->route('mitra.penilaian.create', $pengajuanId)
                ->with('error', 'Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.');
        }

        $request->validate([
            'nilai_absensi' => 'required|numeric|min:0|max:100',
            'nilai_sikap_etika' => 'required|numeric|min:0|max:100',
            'nilai_teamwork' => 'required|numeric|min:0|max:100',
            'nilai_disiplin_tanggung_jawab' => 'required|numeric|min:0|max:100',
            'catatan_mitra' => 'nullable|string|max:1000',
        ]);

        $nilaiLapangan =
            ($request->nilai_absensi * 0.10) +
            ($request->nilai_sikap_etika * 0.15) +
            ($request->nilai_teamwork * 0.15) +
            ($request->nilai_disiplin_tanggung_jawab * 0.20);

        $penilaian = Penilaian::updateOrCreate(
            ['pengajuan_id' => $pengajuanId],
            [
                'nilai_lapangan' => round($nilaiLapangan, 2),
                'nilai_absensi' => $request->nilai_absensi,
                'nilai_sikap_etika' => $request->nilai_sikap_etika,
                'nilai_teamwork' => $request->nilai_teamwork,
                'nilai_disiplin_tanggung_jawab' => $request->nilai_disiplin_tanggung_jawab,
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
                'judul' => 'Nilai Lapangan Tersimpan',
                'pesan' => 'Nilai lapangan Anda telah diperbarui oleh Mitra/Instansi. Nilai akhir akan tampil setelah semua komponen lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('mitra.penilaian.index')->with('success', 'Nilai lapangan berhasil disimpan.');
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
            if ($date->isWeekday()) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    private function baseQueryForMitra()
    {
        return PengajuanMagang::query()
            ->where('mitra_id', auth()->user()->mitraUser?->mitra_id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);
    }

    private function findPengajuanForMitra($pengajuanId): PengajuanMagang
    {
        return $this->baseQueryForMitra()
            ->with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian', 'absensis'])
            ->findOrFail($pengajuanId);
    }
}
