<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\KelayakanSeminar;
use App\Models\PengajuanMagang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    public function index()
    {
        $pembimbingId = auth()->user()->pembimbingLapangan?->id;
        $kelayakans = KelayakanSeminar::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode'])
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->latest()
            ->paginate(15);
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'kelayakanSeminar'])
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->whereIn('status_seminar', ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda'])
            ->latest()
            ->paginate(10, ['*'], 'jadwal_page');
        return view('pembimbing.seminar.index', compact('kelayakans', 'pengajuans'));
    }

    public function show(KelayakanSeminar $kelayakan)
    {
        abort_unless($kelayakan->pembimbing_lapangan_id === auth()->user()->pembimbingLapangan?->id, 403);
        $kelayakan->load(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.bimbinganFormals', 'pengajuan.bimbingans.dosen']);
        return view('pembimbing.seminar.show', compact('kelayakan'));
    }

    public function validasi(Request $request, KelayakanSeminar $kelayakan)
    {
        abort_unless($kelayakan->pembimbing_lapangan_id === auth()->user()->pembimbingLapangan?->id, 403);
        $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'required_if:status,revisi,ditolak|nullable|string|max:1000',
        ]);
        $kelayakan->update([
            'status_persetujuan_pembimbing' => $request->status,
            'catatan_pembimbing' => $request->catatan,
            'tanggal_persetujuan_pembimbing' => $request->status === 'disetujui' ? now() : null,
        ]);
        if ($kelayakan->mahasiswa?->user) {
            Notifikasi::create(['user_id' => $kelayakan->mahasiswa->user->id, 'judul' => 'Status Kelayakan Seminar dari Pembimbing Lapangan', 'pesan' => 'Pembimbing lapangan memberi status ' . $request->status . ' pada bahan kelayakan seminar Anda.', 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
            if ($kelayakan->fresh()->isApproved()) {
                Notifikasi::create(['user_id' => $kelayakan->mahasiswa->user->id, 'judul' => 'Seminar Sudah Bisa Diajukan', 'pesan' => 'Bahan kelayakan seminar sudah disetujui dosen dan pembimbing lapangan.', 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
            }
        }
        return redirect()->back()->with('success', 'Status kelayakan seminar berhasil disimpan.');
    }

    public function file(KelayakanSeminar $kelayakan, string $type)
    {
        abort_unless($kelayakan->pembimbing_lapangan_id === auth()->user()->pembimbingLapangan?->id, 403);
        $path = $type === 'produk' ? $kelayakan->produk_magang : $kelayakan->laporan_hasil_magang;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return response()->file(Storage::disk('public')->path($path), ['Content-Disposition' => 'inline']);
    }
}