<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Notifikasi;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
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

        $request->validate($this->rules());

        $penilaian = Penilaian::updateOrCreate(
            ['pengajuan_id' => $pengajuanId],
            [
                'dosen_kehadiran_disiplin' => $request->dosen_kehadiran_disiplin,
                'dosen_kinerja_sikap' => $request->dosen_kinerja_sikap,
                'dosen_logbook_kegiatan' => $request->dosen_logbook_kegiatan,
                'dosen_luaran' => $request->dosen_luaran,
                'dosen_laporan_akhir' => $request->dosen_laporan_akhir,
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
                'judul' => 'Nilai Dosen Pembimbing Tersimpan',
                'pesan' => 'Nilai dari dosen pembimbing telah diperbarui. Nilai akhir tampil setelah nilai pembimbing lapangan juga lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('dosen.penilaian.index')->with('success', 'Nilai dosen pembimbing berhasil disimpan.');
    }

    private function rules(): array
    {
        return [
            'dosen_kehadiran_disiplin' => 'required|numeric|min:0|max:100',
            'dosen_kinerja_sikap' => 'required|numeric|min:0|max:100',
            'dosen_logbook_kegiatan' => 'required|numeric|min:0|max:100',
            'dosen_luaran' => 'required|numeric|min:0|max:100',
            'dosen_laporan_akhir' => 'required|numeric|min:0|max:100',
            'catatan_dosen' => 'nullable|string|max:1000',
        ];
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