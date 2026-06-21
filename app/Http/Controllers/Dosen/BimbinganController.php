<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\BimbinganFormal;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $dosenId    = auth()->user()->dosen?->id;
        $bimbingans = Bimbingan::where('dosen_id', $dosenId)
            ->with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode', 'pengajuan.logbooks'])
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']))
            ->get();
        return view('dosen.bimbingan.index', compact('bimbingans'));
    }

    public function show($pengajuanId)
    {
        $bimbingan = $this->findAssignment($pengajuanId);
        $bimbingan->load(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode', 'pengajuan.dokumens', 'pengajuan.bimbingans.dosen']);
        return view('dosen.bimbingan.show', compact('bimbingan'));
    }

    public function formalIndex($pengajuanId)
    {
        $bimbingan = $this->findAssignment($pengajuanId);
        $riwayat = BimbinganFormal::where('pengajuan_id', $pengajuanId)
            ->where('dosen_id', auth()->user()->dosen?->id)
            ->latest()
            ->paginate(10);

        return view('dosen.bimbingan.formal-index', compact('bimbingan', 'riwayat'));
    }

    public function formalShow(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->dosen_id, auth()->user()->dosen?->id), 403);
        $bimbingan->load(['pengajuan.mahasiswa', 'pengajuan.mitra']);
        return view('dosen.bimbingan.formal-show', compact('bimbingan'));
    }

    public function file(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->dosen_id, auth()->user()->dosen?->id), 403);

        return $this->publicInlineResponse($bimbingan->lampiran, basename($this->normalizePublicPath($bimbingan->lampiran) ?: $bimbingan->lampiran));
    }
    public function reply(Request $request, BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->dosen_id, auth()->user()->dosen?->id), 403);
        $request->validate([
            'balasan_dosen' => 'required|string|max:2000',
        ]);

        $bimbingan->update([
            'balasan_dosen' => $request->balasan_dosen,
            'status' => 'selesai',
        ]);

        if ($bimbingan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $bimbingan->mahasiswa->user->id,
                'judul' => 'Balasan Bimbingan',
                'pesan' => 'Dosen pembimbing telah membalas bimbingan Anda.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.bimbingan.show', $bimbingan->id),
            ]);
        }

        return redirect()->back()->with('success', 'Balasan bimbingan berhasil disimpan.');
    }

    private function findAssignment($pengajuanId): Bimbingan
    {
        return Bimbingan::where('dosen_id', auth()->user()->dosen?->id)
            ->where('pengajuan_id', $pengajuanId)
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']))
            ->firstOrFail();
    }
}
