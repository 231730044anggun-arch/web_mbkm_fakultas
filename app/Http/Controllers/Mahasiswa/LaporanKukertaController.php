<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LaporanKukerta;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LaporanKukertaController extends Controller
{
    public function index()
    {
        $pengajuan = $this->activePengajuan();
        if (!$pengajuan) {
            return view('mahasiswa.info-butuh-pengajuan', [
                'feature' => 'Laporan Kukerta',
                'message' => 'Laporan Kukerta baru dapat diisi setelah SK Magang diterbitkan.',
            ]);
        }

        $laporan = LaporanKukerta::where('pengajuan_id', $pengajuan->id)->first();

        return view('mahasiswa.laporan-kukerta.index', compact('pengajuan', 'laporan'));
    }

    public function store(Request $request)
    {
        $pengajuan = $this->activePengajuan();
        if (!$pengajuan) {
            return back()->with('error', 'Laporan Kukerta belum dapat disimpan karena SK Magang belum diterbitkan.');
        }

        $existing = LaporanKukerta::where('pengajuan_id', $pengajuan->id)->first();

        $request->validate([
            'lokasi_kukerta' => 'required|string|max:255',
            'target_kukerta' => 'required|string|max:2000',
            'dokumentasi_kukerta.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:10240',
            'laporan_kukerta' => ($existing ? 'nullable' : 'required') . '|file|mimes:pdf|max:10240',
        ]);

        $dokumentasi = $existing?->dokumentasi_kukerta ?? [];
        if ($request->hasFile('dokumentasi_kukerta')) {
            foreach ($request->file('dokumentasi_kukerta') as $file) {
                $dokumentasi[] = $file->store('documents/kukerta/dokumentasi', 'public');
            }
        }

        $laporanPath = $existing?->laporan_kukerta;
        if ($request->hasFile('laporan_kukerta')) {
            if ($laporanPath) Storage::disk('public')->delete($laporanPath);
            $laporanPath = $request->file('laporan_kukerta')->store('documents/kukerta/laporan', 'public');
        }

        $laporan = LaporanKukerta::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'lokasi_kukerta' => $request->lokasi_kukerta,
                'target_kukerta' => $request->target_kukerta,
                'dokumentasi_kukerta' => $dokumentasi,
                'laporan_kukerta' => $laporanPath,
                'status' => 'terkirim',
            ]
        );

        foreach ($pengajuan->bimbingans as $bimbingan) {
            if ($bimbingan->dosen?->user) {
                Notifikasi::create([
                    'user_id' => $bimbingan->dosen->user->id,
                    'judul' => 'Laporan Kukerta Baru',
                    'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim laporan Kukerta.',
                    'status' => 'belum',
                    'target_url' => route('dosen.laporan-kukerta.show', $laporan->id),
                ]);
            }
        }

        return back()->with('success', 'Laporan Kukerta berhasil disimpan.');
    }

    public function file(LaporanKukerta $laporan, string $type, ?int $index = null)
    {
        abort_unless($laporan->mahasiswa_id === auth()->user()->mahasiswaProfile?->id, 403);
        $path = $type === 'laporan'
            ? $laporan->laporan_kukerta
            : ($laporan->dokumentasi_kukerta[$index] ?? null);
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        return $this->inlineFile($path, basename($path));
    }

    private function activePengajuan(): ?PengajuanMagang
    {
        return auth()->user()->mahasiswaProfile?->pengajuans()
            ->with(['dokumens', 'bimbingans.dosen.user', 'mahasiswa'])
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest()
            ->first();
    }

    private function inlineFile(string $path, string $filename)
    {
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);
        return response()->stream(fn() => readfile($absolutePath), 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename),
        ]);
    }
}