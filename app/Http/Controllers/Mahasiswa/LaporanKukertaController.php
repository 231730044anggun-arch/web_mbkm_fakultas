<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\LaporanKukerta;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LaporanKukertaController extends Controller
{
    use HandlesSecurePublicFiles;
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

        $deadline = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif()
            ? $pengajuan->mahasiswa->deadlineLaporanMagang()
            : null;
        $melewatiDeadline = $deadline && (($laporan?->updated_at ?: now())->gt($deadline));

        return view('mahasiswa.laporan-kukerta.index', compact('pengajuan', 'laporan', 'deadline', 'melewatiDeadline'));
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
            'tanggal_mulai_kukerta' => 'required|date',
            'tanggal_selesai_kukerta' => 'required|date|after_or_equal:tanggal_mulai_kukerta',
            'laporan_kukerta' => ($existing ? 'nullable' : 'required') . '|file|mimes:pdf|max:10240',
            'foto_dokumentasi_kukerta' => ($existing && count($existing->foto_dokumentasi_kukerta ?? []) ? 'nullable' : 'required') . '|array',
            'foto_dokumentasi_kukerta.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'output_kukerta_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png|max:20480',
            'output_kukerta_link' => 'nullable|url|max:500',
        ]);

        $laporanPath = $existing?->laporan_kukerta;
        if ($request->hasFile('laporan_kukerta')) {
            if ($laporanPath) $this->deletePublicFileIfExists($laporanPath);
            $laporanPath = $request->file('laporan_kukerta')->store('documents/kukerta/laporan', 'public');
        }

        $fotoPaths = $existing?->foto_dokumentasi_kukerta ?? [];
        if ($request->hasFile('foto_dokumentasi_kukerta')) {
            foreach ($fotoPaths as $oldFoto) {
                $this->deletePublicFileIfExists($oldFoto);
            }
            $fotoPaths = [];
            foreach ($request->file('foto_dokumentasi_kukerta') as $foto) {
                $fotoPaths[] = $foto->store('documents/kukerta/foto', 'public');
            }
        }

        $outputPath = $existing?->output_kukerta_file;
        if ($request->hasFile('output_kukerta_file')) {
            if ($outputPath) $this->deletePublicFileIfExists($outputPath);
            $outputPath = $request->file('output_kukerta_file')->store('documents/kukerta/output', 'public');
        }

        $deadline = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif()
            ? $pengajuan->mahasiswa->deadlineLaporanMagang()
            : null;
        $isLate = $deadline && now()->gt($deadline);

        $laporan = LaporanKukerta::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'lokasi_kukerta' => $request->lokasi_kukerta,
                'target_kukerta' => $request->target_kukerta,
                'tanggal_mulai_kukerta' => $request->tanggal_mulai_kukerta,
                'tanggal_selesai_kukerta' => $request->tanggal_selesai_kukerta,
                'dokumentasi_kukerta' => $existing?->dokumentasi_kukerta ?? [],
                'foto_dokumentasi_kukerta' => $fotoPaths,
                'laporan_kukerta' => $laporanPath,
                'output_kukerta_file' => $outputPath,
                'output_kukerta_link' => $request->output_kukerta_link,
                'status' => $isLate ? 'terlambat' : 'terkirim',
            ]
        );

        if ($laporanPath) {
            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'laporan_kukerta'],
                [
                    'file_path' => $laporanPath,
                    'tanggal_upload' => now()->toDateString(),
                    'status_verifikasi' => 'valid',
                    'catatan' => $isLate ? 'Laporan Kukerta dikirim melewati deadline.' : 'Laporan Kukerta',
                ]
            );
        }

        if ($outputPath) {
            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'output_kukerta'],
                [
                    'file_path' => $outputPath,
                    'tanggal_upload' => now()->toDateString(),
                    'status_verifikasi' => 'valid',
                    'catatan' => 'Output Kukerta',
                ]
            );
        }

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
        abort_unless($this->idsMatch($laporan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);
        $path = match ($type) {
            'laporan' => $laporan->laporan_kukerta,
            'foto' => ($laporan->foto_dokumentasi_kukerta[$index] ?? null),
            'output' => $laporan->output_kukerta_file,
            default => ($laporan->dokumentasi_kukerta[$index] ?? null),
        };
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
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
