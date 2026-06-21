<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\BimbinganFormal;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BimbinganController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $pengajuan = $this->activeGuidancePengajuan();
        if (!$pengajuan) {
            return view('mahasiswa.info-butuh-pengajuan', [
                'feature' => 'Bimbingan',
                'message' => 'Bimbingan belum dapat digunakan karena data penempatan magang belum lengkap atau dokumen SK/Surat Keterangan Magang belum tersedia.',
            ]);
        }

        $bimbingans = BimbinganFormal::with(['dosen', 'pembimbingLapangan'])
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
        if ($pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() && !$pengajuan->penempatanLengkap()) {
            return redirect()->route('mahasiswa.bimbingan.index')->with('error', 'Data penempatan magang Anda belum lengkap. Silakan hubungi admin/fakultas untuk melengkapi dosen pembimbing, pembimbing lapangan, instansi, dan tanggal magang.');
        }

        return view('mahasiswa.bimbingan.create', compact('pengajuan'));
    }

    public function store(Request $request)
    {
        $pengajuan = $this->activeGuidancePengajuan();
        abort_unless($pengajuan, 403);
        if ($pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() && !$pengajuan->penempatanLengkap()) {
            return redirect()->route('mahasiswa.bimbingan.index')->with('error', 'Data penempatan magang Anda belum lengkap. Silakan hubungi admin/fakultas untuk melengkapi dosen pembimbing, pembimbing lapangan, instansi, dan tanggal magang.');
        }

        $request->validate([
            'tanggal_bimbingan' => 'required|date',
            'tujuan_bimbingan' => 'required|in:dosen_pembimbing,pembimbing_lapangan',
            'topik' => 'required|string|max:255',
            'catatan_mahasiswa' => 'nullable|string|max:2000',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $tujuan = $request->tujuan_bimbingan;
        $dosenId = $tujuan === 'dosen_pembimbing' ? $pengajuan->bimbingans->first()?->dosen_id : null;
        $pembimbingId = $tujuan === 'pembimbing_lapangan' ? $pengajuan->pembimbing_lapangan_id : null;
        if ($tujuan === 'dosen_pembimbing' && !$dosenId) {
            return back()->withInput()->with('error', 'Data pembimbing belum tersedia. Silakan hubungi admin.');
        }
        if ($tujuan === 'pembimbing_lapangan' && !$pembimbingId) {
            return back()->withInput()->with('error', 'Data pembimbing belum tersedia. Silakan hubungi admin.');
        }
        $lampiran = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('documents/bimbingan', 'public')
            : null;

        $bimbingan = BimbinganFormal::create([
            'pengajuan_id' => $pengajuan->id,
            'mahasiswa_id' => $pengajuan->mahasiswa_id,
            'tujuan_bimbingan' => $tujuan,
            'dosen_id' => $dosenId,
            'pembimbing_lapangan_id' => $pembimbingId,
            'tanggal_bimbingan' => $request->tanggal_bimbingan,
            'topik' => $request->topik,
            'catatan_mahasiswa' => $request->catatan_mahasiswa,
            'lampiran' => $lampiran,
            'status' => 'menunggu_balasan',
        ]);

        if ($tujuan === 'dosen_pembimbing' && ($dosen = $bimbingan->dosen()->with('user')->first()) && $dosen?->user) {
            Notifikasi::create([
                'user_id' => $dosen->user->id,
                'judul' => 'Bimbingan Baru',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim bimbingan baru.',
                'status' => 'belum',
                'target_url' => route('dosen.bimbingan.formal.show', $bimbingan->id),
            ]);
        }
        if ($tujuan === 'pembimbing_lapangan' && $pengajuan->pembimbingLapangan?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->pembimbingLapangan->user->id,
                'judul' => 'Bimbingan Baru',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim bimbingan baru kepada pembimbing lapangan.',
                'status' => 'belum',
                'target_url' => route('pembimbing.bimbingan.formal.show', $bimbingan->id),
            ]);
        }

        return redirect()->route('mahasiswa.bimbingan.show', $bimbingan->id)->with('success', 'Bimbingan berhasil dikirim.');
    }

    public function show(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);
        $bimbingan->load(['pengajuan.mitra', 'dosen', 'pembimbingLapangan']);

        return view('mahasiswa.bimbingan.show', compact('bimbingan'));
    }

    public function destroy(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);

        if ($bimbingan->status !== 'menunggu_balasan' || filled($bimbingan->balasan_dosen) || filled($bimbingan->balasan_pembimbing)) {
            return redirect()->back()->with('error', 'Bimbingan tidak dapat dihapus karena sudah dibalas.');
        }

        if ($bimbingan->lampiran) {
            $this->deletePublicFileIfExists($bimbingan->lampiran);
        }
        $bimbingan->delete();

        return redirect()->route('mahasiswa.bimbingan.index')->with('success', 'Bimbingan berhasil dihapus.');
    }
    public function download(BimbinganFormal $bimbingan)
    {
        abort_unless($this->idsMatch($bimbingan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);
        return $this->publicDownloadResponse($bimbingan->lampiran, basename($this->normalizePublicPath($bimbingan->lampiran) ?: $bimbingan->lampiran));
    }

    private function activeGuidancePengajuan(): ?PengajuanMagang
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        if (!$mahasiswa) {
            return null;
        }

        $query = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'dokumens'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);

        if (!$mahasiswa->isAngkatanKhususSkKolektif()) {
            $query->whereHas('bimbingans')
                ->whereHas('dokumens', fn($q) => $q
                    ->whereIn('jenis_dokumen', ['surat_keterangan_magang', 'sk_magang'])
                    ->where('status_verifikasi', 'valid')
                    ->whereNotNull('file_path'));
        }

        return $query->latest()->first();
    }
}
