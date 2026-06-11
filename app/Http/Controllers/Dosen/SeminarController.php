<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KelayakanSeminar;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    public function index()
    {
        $dosenId = auth()->user()->dosen?->id;
        $kelayakans = KelayakanSeminar::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode'])
            ->where('dosen_id', $dosenId)
            ->latest()
            ->paginate(15);
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'kelayakanSeminar'])
            ->whereHas('bimbingans', fn($q) => $q->where('dosen_id', $dosenId))
            ->whereIn('status_seminar', ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda'])
            ->latest()
            ->paginate(10, ['*'], 'jadwal_page');
        return view('dosen.seminar.index', compact('kelayakans', 'pengajuans'));
    }

    public function show(KelayakanSeminar $kelayakan)
    {
        abort_unless($kelayakan->dosen_id === auth()->user()->dosen?->id, 403);
        $kelayakan->load(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.bimbinganFormals', 'pengajuan.pembimbingLapangan']);
        return view('dosen.seminar.show', compact('kelayakan'));
    }

    public function validasi(Request $request, KelayakanSeminar $kelayakan)
    {
        abort_unless($kelayakan->dosen_id === auth()->user()->dosen?->id, 403);
        $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'required_if:status,revisi,ditolak|nullable|string|max:1000',
        ]);
        $kelayakan->update([
            'status_persetujuan_dosen' => $request->status,
            'catatan_dosen' => $request->catatan,
            'tanggal_persetujuan_dosen' => $request->status === 'disetujui' ? now() : null,
        ]);
        $this->notifyMahasiswa($kelayakan, 'Status Kelayakan Seminar dari Dosen', 'Dosen pembimbing memberi status ' . $request->status . ' pada bahan kelayakan seminar Anda.');
        if ($kelayakan->fresh()->isApproved()) {
            $this->notifyMahasiswa($kelayakan, 'Seminar Sudah Bisa Diajukan', 'Bahan kelayakan seminar sudah disetujui dosen dan pembimbing lapangan.');
        }
        return redirect()->back()->with('success', 'Status kelayakan seminar berhasil disimpan.');
    }

    public function file(KelayakanSeminar $kelayakan, string $type)
    {
        abort_unless($kelayakan->dosen_id === auth()->user()->dosen?->id, 403);
        $path = $type === 'produk' ? $kelayakan->produk_magang : $kelayakan->laporan_hasil_magang;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return response()->file(Storage::disk('public')->path($path), ['Content-Disposition' => 'inline']);
    }

    private function notifyMahasiswa(KelayakanSeminar $kelayakan, string $judul, string $pesan): void
    {
        if ($kelayakan->mahasiswa?->user) {
            Notifikasi::create(['user_id' => $kelayakan->mahasiswa->user->id, 'judul' => $judul, 'pesan' => $pesan, 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
        }
    }
}