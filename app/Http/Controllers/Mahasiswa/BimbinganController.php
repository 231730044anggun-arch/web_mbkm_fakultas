<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\BimbinganFormal;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BimbinganController extends Controller
{
    public function index()
    {
        $pengajuan = $this->activeGuidancePengajuan();
        if (!$pengajuan) {
            return view('mahasiswa.info-butuh-pengajuan', [
                'feature' => 'Bimbingan',
                'message' => 'Bimbingan belum dapat digunakan karena dosen pembimbing belum ditugaskan atau Surat Keterangan Magang belum diterbitkan.',
            ]);
        }

        $bimbingans = BimbinganFormal::with('dosen')
            ->where('pengajuan_id', $pengajuan->id)
            ->latest()
            ->paginate(10);

        return view('mahasiswa.bimbingan.index', compact('pengajuan', 'bimbingans'));
    }

    public function create()
    {
        $pengajuan = $this->activeGuidancePengajuan();
        if (!$pengajuan) {
            return redirect()->route('mahasiswa.bimbingan.index');
        }

        return view('mahasiswa.bimbingan.create', compact('pengajuan'));
    }

    public function store(Request $request)
    {
        $pengajuan = $this->activeGuidancePengajuan();
        abort_unless($pengajuan, 403);

        $request->validate([
            'tanggal_bimbingan' => 'required|date',
            'topik' => 'required|string|max:255',
            'catatan_mahasiswa' => 'nullable|string|max:2000',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $dosenId = $pengajuan->bimbingans->first()->dosen_id;
        $lampiran = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('documents/bimbingan', 'public')
            : null;

        $bimbingan = BimbinganFormal::create([
            'pengajuan_id' => $pengajuan->id,
            'mahasiswa_id' => $pengajuan->mahasiswa_id,
            'dosen_id' => $dosenId,
            'tanggal_bimbingan' => $request->tanggal_bimbingan,
            'topik' => $request->topik,
            'catatan_mahasiswa' => $request->catatan_mahasiswa,
            'lampiran' => $lampiran,
            'status' => 'menunggu_balasan',
        ]);

        $dosen = $bimbingan->dosen()->with('user')->first();
        if ($dosen?->user) {
            Notifikasi::create([
                'user_id' => $dosen->user->id,
                'judul' => 'Bimbingan Baru',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim bimbingan baru.',
                'status' => 'belum',
                'target_url' => route('dosen.bimbingan.formal.show', $bimbingan->id),
            ]);
        }

        return redirect()->route('mahasiswa.bimbingan.show', $bimbingan->id)->with('success', 'Bimbingan berhasil dikirim.');
    }

    public function show(BimbinganFormal $bimbingan)
    {
        abort_unless($bimbingan->mahasiswa_id === auth()->user()->mahasiswaProfile?->id, 403);
        $bimbingan->load(['pengajuan.mitra', 'dosen']);

        return view('mahasiswa.bimbingan.show', compact('bimbingan'));
    }

    public function destroy(BimbinganFormal $bimbingan)
    {
        abort_unless($bimbingan->mahasiswa_id === auth()->user()->mahasiswaProfile?->id, 403);

        if ($bimbingan->status !== 'menunggu_balasan' || filled($bimbingan->balasan_dosen)) {
            return redirect()->back()->with('error', 'Bimbingan tidak dapat dihapus karena sudah dibalas dosen.');
        }

        if ($bimbingan->lampiran) {
            Storage::disk('public')->delete($bimbingan->lampiran);
        }
        $bimbingan->delete();

        return redirect()->route('mahasiswa.bimbingan.index')->with('success', 'Bimbingan berhasil dihapus.');
    }
    public function download(BimbinganFormal $bimbingan)
    {
        abort_unless($bimbingan->mahasiswa_id === auth()->user()->mahasiswaProfile?->id, 403);
        abort_if(!$bimbingan->lampiran || !Storage::disk('public')->exists($bimbingan->lampiran), 404);

        return Storage::disk('public')->download($bimbingan->lampiran, basename($bimbingan->lampiran));
    }

    private function activeGuidancePengajuan(): ?PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'bimbingans.dosen.user', 'dokumens'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->whereHas('bimbingans')
            ->whereHas('dokumens', fn($q) => $q
                ->where('jenis_dokumen', 'surat_keterangan_magang')
                ->where('status_verifikasi', 'valid')
                ->whereNotNull('file_path'))
            ->latest()
            ->first();
    }
}
