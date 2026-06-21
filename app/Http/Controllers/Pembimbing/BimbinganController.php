<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\BimbinganFormal;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $pembimbingId = auth()->user()->pembimbingLapangan?->id;
        abort_unless($pembimbingId, 403);

        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'bimbinganFormals' => fn($q) => $q->where('pembimbing_lapangan_id', $pembimbingId)])
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest('updated_at')
            ->paginate(15);

        return view('pembimbing.bimbingan.index', compact('pengajuans'));
    }

    public function formalIndex($pengajuanId)
    {
        $pembimbingId = auth()->user()->pembimbingLapangan?->id;
        abort_unless($pembimbingId, 403);

        $pengajuan = PengajuanMagang::with(['mahasiswa.prodi', 'mitra'])
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->findOrFail($pengajuanId);

        $riwayat = BimbinganFormal::where('pengajuan_id', $pengajuan->id)
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->latest()
            ->paginate(10);

        return view('pembimbing.bimbingan.formal-index', compact('pengajuan', 'riwayat'));
    }

    public function formalShow(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->pembimbing_lapangan_id, auth()->user()->pembimbingLapangan?->id), 403);
        $bimbingan->load(['pengajuan.mahasiswa', 'pengajuan.mitra', 'pembimbingLapangan']);

        return view('pembimbing.bimbingan.formal-show', compact('bimbingan'));
    }

    public function file(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->pembimbing_lapangan_id, auth()->user()->pembimbingLapangan?->id), 403);

        return $this->publicInlineResponse($bimbingan->lampiran, basename($this->normalizePublicPath($bimbingan->lampiran) ?: $bimbingan->lampiran));
    }
    public function reply(Request $request, BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->pembimbing_lapangan_id, auth()->user()->pembimbingLapangan?->id), 403);
        $request->validate([
            'balasan_pembimbing' => 'required|string|max:2000',
        ]);

        $bimbingan->update([
            'balasan_pembimbing' => $request->balasan_pembimbing,
            'status' => 'selesai',
        ]);

        if ($bimbingan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $bimbingan->mahasiswa->user->id,
                'judul' => 'Balasan Bimbingan Pembimbing Lapangan',
                'pesan' => 'Pembimbing lapangan telah membalas bimbingan Anda.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.bimbingan.show', $bimbingan->id),
            ]);
        }

        return redirect()->back()->with('success', 'Balasan bimbingan berhasil disimpan.');
    }
}
