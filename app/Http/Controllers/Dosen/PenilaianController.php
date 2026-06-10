<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
use App\Models\Notifikasi;
use App\Models\Bimbingan;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function index()
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless($dosenId, 403);

        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian'])
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->whereHas('bimbingans', fn($query) => $query->where('dosen_id', $dosenId))
            ->latest('updated_at')
            ->paginate(15);

        return view('dosen.penilaian.index', compact('pengajuans'));
    }

    public function create($pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);
        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $canInput = $pengajuan->hasValidSeminar();

        return view('dosen.penilaian.create', compact('pengajuan', 'penilaian', 'canInput'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);

        if (!$pengajuan->hasValidSeminar()) {
            return redirect()->route('dosen.penilaian.create', $pengajuanId)
                ->with('error', 'Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.');
        }

        $request->validate([
            'nilai_logbook' => 'required|numeric|min:0|max:100',
            'nilai_presentasi' => 'required|numeric|min:0|max:100',
            'catatan_dosen' => 'nullable|string|max:1000',
        ]);

        $nilaiAkademik = ($request->nilai_logbook * 0.10) + ($request->nilai_presentasi * 0.30);

        $penilaian = Penilaian::updateOrCreate(
            ['pengajuan_id' => $pengajuanId],
            [
                'nilai_logbook' => $request->nilai_logbook,
                'nilai_presentasi' => $request->nilai_presentasi,
                'nilai_seminar' => $request->nilai_presentasi,
                'nilai_dosen' => round($nilaiAkademik, 2),
                'catatan_dosen' => $request->catatan_dosen,
                'catatan' => $request->catatan_dosen,
            ]
        );

        $penilaian->calculateFinalScore();

        if ($penilaian->nilai_akhir !== null && $pengajuan->status_pengajuan !== 'selesai') {
            $pengajuan->update(['status_pengajuan' => 'selesai']);
        }

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Nilai Akademik Tersimpan',
                'pesan' => 'Nilai akademik Anda telah diperbarui oleh dosen pembimbing. Nilai akhir akan tampil setelah semua komponen lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('dosen.penilaian.index')->with('success', 'Nilai akademik berhasil disimpan.');
    }

    private function findPengajuanForDosen($pengajuanId): PengajuanMagang
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless(Bimbingan::where('dosen_id', $dosenId)
            ->where('pengajuan_id', $pengajuanId)
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']))
            ->exists(), 403);

        return PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian'])->findOrFail($pengajuanId);
    }
}
