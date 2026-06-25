<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\LaporanKukerta;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if ($existing) {
            return $this->persist($request, $pengajuan, $existing, true);
        }

        return $this->persist($request, $pengajuan, null, false);
    }

    public function update(Request $request, LaporanKukerta $laporan)
    {
        $pengajuan = $this->activePengajuan();
        abort_unless($pengajuan && $this->idsMatch($laporan->pengajuan_id, $pengajuan->id) && $this->idsMatch($laporan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);

        return $this->persist($request, $pengajuan, $laporan, true);
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

    private function persist(Request $request, PengajuanMagang $pengajuan, ?LaporanKukerta $existing, bool $isUpdate)
    {
        $hasExistingPhotos = count($existing?->foto_dokumentasi_kukerta ?? []) > 0;

        $request->validate([
            'lokasi_kukerta' => 'required|string|max:255',
            'target_kukerta' => 'required|string|max:2000',
            'tanggal_mulai_kukerta' => 'required|date',
            'tanggal_selesai_kukerta' => 'required|date|after_or_equal:tanggal_mulai_kukerta',
            'laporan_kukerta' => ($existing ? 'nullable' : 'required') . '|file|mimes:pdf|max:51200',
            'foto_dokumentasi_kukerta' => ($hasExistingPhotos ? 'nullable' : 'required') . '|array',
            'foto_dokumentasi_kukerta.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,zip|max:102400',
            'output_kukerta_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png|max:102400',
            'output_kukerta_link' => 'nullable|url|max:500',
        ], [
            'laporan_kukerta.required' => 'Laporan Kukerta PDF wajib diunggah.',
            'laporan_kukerta.mimes' => 'Laporan Kukerta harus berupa PDF.',
            'laporan_kukerta.max' => 'Ukuran Laporan Kukerta maksimal 50 MB.',
            'foto_dokumentasi_kukerta.required' => 'Foto Dokumentasi Kegiatan Kukerta wajib diunggah.',
            'foto_dokumentasi_kukerta.*.max' => 'Ukuran Foto/Dokumentasi Kukerta maksimal 100 MB per file.',
            'foto_dokumentasi_kukerta.*.mimes' => 'Format Dokumentasi Kukerta harus JPG, JPEG, PNG, WEBP, PDF, atau ZIP.',
            'output_kukerta_file.max' => 'Ukuran Output Kukerta maksimal 100 MB.',
            'output_kukerta_file.mimes' => 'Format Output Kukerta harus PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, ZIP, RAR, JPG, JPEG, atau PNG.',
        ]);

        $newFiles = [];
        $oldFilesToDelete = [];

        try {
            $laporanPath = $existing?->laporan_kukerta;
            if ($request->hasFile('laporan_kukerta')) {
                $newFiles['laporan'] = $request->file('laporan_kukerta')->store('documents/kukerta/laporan', 'public');
                $laporanPath = $newFiles['laporan'];
                if ($existing?->laporan_kukerta) {
                    $oldFilesToDelete[] = $existing->laporan_kukerta;
                }
            }

            $fotoPaths = $existing?->foto_dokumentasi_kukerta ?? [];
            if ($request->hasFile('foto_dokumentasi_kukerta')) {
                foreach ($request->file('foto_dokumentasi_kukerta') as $foto) {
                    if ($foto) {
                        $path = $foto->store('documents/kukerta/foto', 'public');
                        $newFiles[] = $path;
                        $fotoPaths[] = $path;
                    }
                }
            }

            $outputPath = $existing?->output_kukerta_file;
            if ($request->hasFile('output_kukerta_file')) {
                $newFiles['output'] = $request->file('output_kukerta_file')->store('documents/kukerta/output', 'public');
                $outputPath = $newFiles['output'];
                if ($existing?->output_kukerta_file) {
                    $oldFilesToDelete[] = $existing->output_kukerta_file;
                }
            }

            $deadline = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif()
                ? $pengajuan->mahasiswa->deadlineLaporanMagang()
                : null;
            $isLate = $deadline && now()->gt($deadline);

            $laporan = DB::transaction(function () use ($pengajuan, $existing, $request, $laporanPath, $fotoPaths, $outputPath, $isLate) {
                $laporan = LaporanKukerta::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'mahasiswa_id' => $pengajuan->mahasiswa_id,
                        'lokasi_kukerta' => $request->lokasi_kukerta,
                        'target_kukerta' => $request->target_kukerta,
                        'tanggal_mulai_kukerta' => $request->tanggal_mulai_kukerta,
                        'tanggal_selesai_kukerta' => $request->tanggal_selesai_kukerta,
                        'dokumentasi_kukerta' => $existing?->dokumentasi_kukerta ?? [],
                        'foto_dokumentasi_kukerta' => array_values($fotoPaths),
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

                return $laporan->fresh();
            });

            foreach ($oldFilesToDelete as $oldFile) {
                $this->deletePublicFileIfExists($oldFile);
            }

            if (!$isUpdate) {
                $this->notifyDosenPembimbing($pengajuan, $laporan);
            }

            return redirect()
                ->route('mahasiswa.laporan-kukerta.index')
                ->with('success', $isUpdate ? 'Laporan Kukerta berhasil diperbarui.' : 'Laporan Kukerta berhasil disimpan.');
        } catch (\Throwable $e) {
            foreach ($newFiles as $path) {
                $this->deletePublicFileIfExists($path);
            }

            Log::error('Gagal menyimpan Laporan Kukerta', [
                'pengajuan_id' => $pengajuan->id,
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'laporan_id' => $existing?->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Laporan Kukerta belum dapat disimpan. Silakan periksa data dan file yang diunggah, lalu coba lagi.');
        }
    }

    private function notifyDosenPembimbing(PengajuanMagang $pengajuan, LaporanKukerta $laporan): void
    {
        try {
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
        } catch (\Throwable $e) {
            Log::warning('Notifikasi Laporan Kukerta gagal dikirim', [
                'laporan_id' => $laporan->id,
                'message' => $e->getMessage(),
            ]);
        }
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
}
