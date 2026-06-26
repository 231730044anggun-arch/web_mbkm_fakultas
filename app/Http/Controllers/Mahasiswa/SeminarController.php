<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\KelayakanSeminar;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDF;

class SeminarController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        if (!$mahasiswa) return view('mahasiswa.info-butuh-pengajuan', ['feature' => 'Seminar Magang']);

        $pengajuans = PengajuanMagang::with([
            'mitra', 'periode', 'dokumens', 'logbooks', 'absensis',
            'bimbingans.dosen.user', 'bimbinganFormals', 'pembimbingLapangan.user', 'kelayakanSeminar.catatanHistories.user'
        ])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest()
            ->get();

        $eligibility = $pengajuans->mapWithKeys(fn($pengajuan) => [$pengajuan->id => $this->seminarEligibility($pengajuan)]);
        return view('mahasiswa.seminar.index', compact('pengajuans', 'eligibility'));
    }

    public function createKelayakan($pengajuanId)
    {
        $pengajuan = $this->findOwnedActiveKelayakanPengajuan($pengajuanId);
        if (!$pengajuan) {
            return redirect()
                ->route('mahasiswa.seminar.index')
                ->with('error', 'Kelayakan seminar belum dapat dikirim karena data penempatan magang belum tersedia. Silakan hubungi admin.');
        }

        $kelayakan = $pengajuan->kelayakanSeminar;

        return view('mahasiswa.seminar.kelayakan-form', compact('pengajuan', 'kelayakan'));
    }

    public function storeKelayakan(Request $request, $pengajuanId)
    {
        $requestId = uniqid('kelayakan_', true);
        Log::info('Kelayakan seminar submit masuk', [
            'request_id' => $requestId,
            'user_id' => auth()->id(),
            'pengajuan_id' => $pengajuanId,
            'marker' => $request->input('kelayakan_form_marker'),
            'request_method' => $request->method(),
            'request_keys' => array_keys($request->except(['laporan_hasil_magang', 'laporan_hasil_magang_final', 'produk_magang', 'draft_jurnal'])),
            'has_laporan' => $request->hasFile('laporan_hasil_magang') || $request->hasFile('laporan_hasil_magang_final'),
            'has_produk' => $request->hasFile('produk_magang'),
            'has_jurnal' => $request->hasFile('draft_jurnal'),
        ]);

        $pengajuan = $this->findOwnedActiveKelayakanPengajuan($pengajuanId);
        if (!$pengajuan) {
            return redirect()
                ->route('mahasiswa.seminar.index')
                ->with('error', 'Kelayakan seminar belum dapat dikirim karena data penempatan magang belum tersedia. Silakan hubungi admin.');
        }

        if ($this->kelayakanDeadlinePassed($pengajuan)) {
            return redirect()
                ->route('mahasiswa.seminar.kelayakan.create', $pengajuan->id)
                ->withInput()
                ->with('error', 'Kelayakan seminar belum dapat dikirim karena deadline pengajuan sudah lewat.');
        }

        if ($request->hasFile('laporan_hasil_magang_final') && !$request->hasFile('laporan_hasil_magang')) {
            $request->files->set('laporan_hasil_magang', $request->file('laporan_hasil_magang_final'));
        }

        $request->validate([
            'judul_laporan' => 'nullable|string|max:255',
            'output_magang' => 'nullable|string',
            'catatan_mahasiswa' => 'nullable|string|max:1000',
        ], [
            'judul_laporan.max' => 'Judul laporan magang maksimal 255 karakter.',
            'catatan_mahasiswa.max' => 'Catatan tambahan maksimal 1000 karakter.',
        ]);

        $dosenId = $this->resolveSingleDosenId($pengajuan);
        $pembimbingLapanganId = $this->resolveSinglePembimbingLapanganId($pengajuan);

        Log::info('Kelayakan seminar sebelum simpan', [
            'request_id' => $requestId,
            'mahasiswa_id' => $pengajuan->mahasiswa_id,
            'pengajuan_id' => $pengajuan->id,
            'dosen_id' => $dosenId,
            'pembimbing_lapangan_id' => $pembimbingLapanganId,
        ]);

        $reviewerPerluVerifikasi = !$dosenId || !$pembimbingLapanganId;
        if ($reviewerPerluVerifikasi) {
            Log::warning('Kelayakan seminar relasi reviewer perlu verifikasi admin', [
                'request_id' => $requestId,
                'pengajuan_id' => $pengajuan->id,
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'dosen_id' => $dosenId,
                'pembimbing_lapangan_id' => $pembimbingLapanganId,
            ]);
        }

        $old = $pengajuan->kelayakanSeminar;
        try {
            if (filled($request->judul_laporan)) {
                $pengajuan->update(['judul_laporan' => $request->judul_laporan]);
            }

            $payload = [
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'dosen_id' => $dosenId,
                'pembimbing_lapangan_id' => $pembimbingLapanganId,
                'laporan_hasil_magang' => $old?->laporan_hasil_magang,
                'output_magang' => $request->filled('output_magang') ? $request->output_magang : $old?->output_magang,
                'produk_magang' => $old?->produk_magang,
                'draft_jurnal' => $old?->draft_jurnal,
                'catatan_mahasiswa' => $request->filled('catatan_mahasiswa') ? $request->catatan_mahasiswa : $old?->catatan_mahasiswa,
                'status' => 'menunggu_persetujuan',
                'status_persetujuan_dosen' => 'menunggu',
                'catatan_dosen' => null,
                'tanggal_persetujuan_dosen' => null,
                'status_persetujuan_pembimbing' => 'menunggu',
                'catatan_pembimbing' => null,
                'tanggal_persetujuan_pembimbing' => null,
            ];

            if (Schema::hasColumn('kelayakan_seminars', 'submitted_at')) {
                $payload['submitted_at'] = now();
            }

            $kelayakan = KelayakanSeminar::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                $payload
            );

            $warnings = $this->storeKelayakanFilesSafely($request, $kelayakan, $requestId);
            $kelayakan = $kelayakan->fresh(['pengajuan.mahasiswa', 'dosen.user', 'pembimbingLapangan.user']);

            Log::info('Kelayakan seminar berhasil disimpan', [
                'request_id' => $requestId,
                'kelayakan_id' => $kelayakan->id,
                'pengajuan_id' => $kelayakan->pengajuan_id,
                'status' => $kelayakan->status,
                'status_persetujuan_dosen' => $kelayakan->status_persetujuan_dosen,
                'status_persetujuan_pembimbing' => $kelayakan->status_persetujuan_pembimbing,
            ]);

            $this->sendKelayakanNotifications($kelayakan);

            $needsVerification = $reviewerPerluVerifikasi || count($warnings) > 0 || !$kelayakan->laporan_hasil_magang || !$kelayakan->produk_magang || !$kelayakan->draft_jurnal || blank($kelayakan->output_magang);
            $message = $needsVerification
                ? 'Kelayakan seminar berhasil dikirim. Dokumen atau data pembimbing yang belum lengkap akan diverifikasi kemudian.'
                : 'Kelayakan seminar berhasil dikirim dan sedang menunggu persetujuan.';

            $redirect = redirect()
                ->route('mahasiswa.seminar.index')
                ->with('success', $message);

            if (count($warnings) > 0) {
                $redirect->with('warning', implode(' ', $warnings));
            }

            return $redirect;
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan kelayakan seminar', [
                'request_id' => $requestId,
                'pengajuan_id' => $pengajuan->id,
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Kelayakan seminar belum dapat dikirim karena server tidak dapat menyimpan data pengajuan. Silakan coba lagi atau hubungi admin dengan kode: ' . $requestId . '.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengajuan_id' => 'required|exists:pengajuan_magangs,id',
            'judul_laporan' => 'required|string|max:255',
            'laporan_seminar' => 'required|file|mimes:pdf|max:51200',
            'catatan' => 'nullable|string|max:500',
        ], [
            'laporan_seminar.max' => 'Ukuran Laporan Seminar maksimal 50 MB.',
        ]);

        $pengajuan = $this->findOwnedPengajuan($request->pengajuan_id);
        $eligibility = $this->seminarEligibility($pengajuan);
        if (!$eligibility['allowed']) return redirect()->back()->with('error', implode(' ', $eligibility['reasons']))->withInput();

        Dokumen::create([
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => 'laporan',
            'file_path' => $request->file('laporan_seminar')->store('documents/laporan-seminar', 'public'),
            'tanggal_upload' => now()->toDateString(),
            'status_verifikasi' => 'valid',
        ]);

        $pengajuan->update([
            'judul_laporan' => $request->judul_laporan,
            'status_laporan' => 'valid',
            'status_seminar' => 'menunggu_jadwal',
            'catatan_mahasiswa' => $request->catatan,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_diajukan',
            'keterangan' => $request->catatan ?: 'Mahasiswa mengajukan seminar magang.',
            'updated_by' => auth()->id(),
        ]);

        foreach (User::whereIn('role', ['admin', 'superadmin'])->get() as $admin) {
            $this->notify($admin->id, 'Pengajuan Seminar Magang', 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengajukan seminar magang.', route('admin.seminar.index'));
        }

        return redirect()->route('mahasiswa.seminar.index')->with('success', 'Pengajuan seminar berhasil dikirim dan menunggu jadwal admin.');
    }

    public function cancel($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        if ($pengajuan->status_seminar !== 'menunggu_jadwal' || $pengajuan->seminar_tanggal || $pengajuan->penilaian()->exists()) {
            return redirect()->back()->with('error', 'Seminar tidak dapat dibatalkan karena sudah dijadwalkan/selesai atau nilai sudah masuk.');
        }
        $pengajuan->update(['status_seminar' => 'dibatalkan']);
        StatusHistory::create(['pengajuan_id' => $pengajuan->id, 'status' => 'seminar_dibatalkan', 'keterangan' => 'Pengajuan seminar dibatalkan oleh mahasiswa.', 'updated_by' => auth()->id()]);
        return redirect()->route('mahasiswa.seminar.index')->with('success', 'Pengajuan seminar berhasil dibatalkan.');
    }

    public function file($kelayakanId, string $type)
    {
        $kelayakan = KelayakanSeminar::with('pengajuan')->findOrFail($kelayakanId);
        abort_unless($this->idsMatch($kelayakan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);
        return $this->fileResponse($kelayakan, $type, false);
    }

    public function downloadSurat($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        abort_unless(in_array($pengajuan->status_seminar, ['terjadwal', 'selesai'], true), 403);
        $dokumen = Dokumen::where('pengajuan_id', $pengajuan->id)->where('jenis_dokumen', 'surat_seminar')->where('status_verifikasi', 'valid')->latest()->first();
        if ($dokumen && $this->publicFileExists($dokumen->file_path)) return $this->publicDownloadResponse($dokumen->file_path);
        $html = view('surat.sk_seminar', compact('pengajuan'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $path = 'surat/sk_seminar_' . $pengajuan->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        Dokumen::updateOrCreate(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_seminar'], ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']);
        return $this->publicDownloadResponse($path);
    }

    private function findOwnedPengajuan($pengajuanId): PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa.user', 'dokumens', 'logbooks', 'absensis', 'bimbingans.dosen.user', 'bimbinganFormals', 'mitra', 'pembimbingLapangan.user', 'kelayakanSeminar.catatanHistories.user'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);
    }

    private function findOwnedActiveKelayakanPengajuan($pengajuanId): ?PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa.user', 'dokumens', 'logbooks', 'absensis', 'bimbingans.dosen.user', 'bimbinganFormals', 'mitra', 'pembimbingLapangan.user', 'kelayakanSeminar.catatanHistories.user'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->find($pengajuanId);
    }

    private function kelayakanDeadlinePassed(PengajuanMagang $pengajuan): bool
    {
        if (!($pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() ?? false)) {
            return false;
        }

        $deadline = $pengajuan->mahasiswa?->deadlineLaporanMagang();
        return $deadline ? now()->gt($deadline) : false;
    }

    private function storeKelayakanFilesSafely(Request $request, KelayakanSeminar $kelayakan, string $requestId): array
    {
        $warnings = [];
        $maxBytes = 102400 * 1024;
        $files = [
            'laporan_hasil_magang' => [
                'label' => 'Laporan Hasil Magang Final',
                'column' => 'laporan_hasil_magang',
                'directory' => 'documents/kelayakan-seminar/laporan',
                'allowed' => ['pdf'],
                'dokumen' => 'laporan_hasil_magang',
                'catatan' => 'Laporan Hasil Magang dari Kelayakan Seminar',
            ],
            'produk_magang' => [
                'label' => 'Produk Magang',
                'column' => 'produk_magang',
                'directory' => 'documents/kelayakan-seminar/produk',
                'allowed' => ['pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'],
                'dokumen' => 'produk_magang',
                'catatan' => 'Produk Magang dari Kelayakan Seminar',
            ],
            'draft_jurnal' => [
                'label' => 'Draft Jurnal',
                'column' => 'draft_jurnal',
                'directory' => 'documents/kelayakan-seminar/draft-jurnal',
                'allowed' => ['pdf', 'doc', 'docx'],
                'dokumen' => 'draft_jurnal',
                'catatan' => 'Draft Jurnal dari Kelayakan Seminar',
            ],
        ];

        foreach ($files as $field => $config) {
            $file = $request->files->get($field);

            if (!$file) {
                if (!$kelayakan->{$config['column']}) {
                    $warnings[] = $config['label'] . ' belum diunggah.';
                }
                continue;
            }

            Log::info('Kelayakan seminar file diterima', [
                'request_id' => $requestId,
                'kelayakan_id' => $kelayakan->id,
                'pengajuan_id' => $kelayakan->pengajuan_id,
                'field' => $field,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'error_code' => method_exists($file, 'getError') ? $file->getError() : null,
            ]);

            if (!$file || !$file->isValid()) {
                $warnings[] = $this->uploadErrorMessage($config['label'], method_exists($file, 'getError') ? $file->getError() : null);
                continue;
            }

            if ($file->getSize() > $maxBytes) {
                $warnings[] = 'Ukuran file ' . $config['label'] . ' maksimal 100 MB.';
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $config['allowed'], true)) {
                $warnings[] = 'Format file ' . $config['label'] . ' tidak didukung.';
                continue;
            }

            try {
                $oldPath = $kelayakan->{$config['column']};
                $path = $file->store($config['directory'], 'public');
                if (!$path) {
                    $warnings[] = 'File ' . $config['label'] . ' tidak dapat disimpan di server. Silakan coba kembali atau hubungi admin.';
                    continue;
                }
                $kelayakan->update([$config['column'] => $path]);
                $this->syncDokumenArsip($kelayakan->pengajuan_id, $config['dokumen'], $path, $config['catatan']);

                if ($oldPath && $oldPath !== $path) {
                    $this->deletePublicFileIfExists($oldPath);
                }
            } catch (\Throwable $e) {
                Log::warning('Upload file kelayakan seminar gagal diproses', [
                    'request_id' => $requestId,
                    'kelayakan_id' => $kelayakan->id,
                    'pengajuan_id' => $kelayakan->pengajuan_id,
                    'field' => $field,
                    'size' => $file->getSize(),
                    'error_code' => method_exists($file, 'getError') ? $file->getError() : null,
                    'message' => $e->getMessage(),
                ]);
                $warnings[] = 'File ' . $config['label'] . ' tidak dapat disimpan di server. Silakan coba kembali atau hubungi admin.';
            }
        }

        return $warnings;
    }

    private function uploadErrorMessage(string $label, ?int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file ' . $label . ' melebihi batas server. Maksimal 100 MB per file.',
            UPLOAD_ERR_PARTIAL => 'File ' . $label . ' hanya terunggah sebagian. Silakan unggah ulang.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server tidak memiliki folder sementara untuk upload. Silakan hubungi admin.',
            UPLOAD_ERR_CANT_WRITE => 'File ' . $label . ' tidak dapat ditulis ke server. Silakan hubungi admin.',
            UPLOAD_ERR_EXTENSION => 'Upload file ' . $label . ' dihentikan oleh ekstensi server. Silakan hubungi admin.',
            default => 'File ' . $label . ' belum berhasil diterima oleh server. Silakan unggah ulang.',
        };
    }

    private function resolveSingleDosenId(PengajuanMagang $pengajuan): ?int
    {
        $candidateIds = $pengajuan->bimbingans
            ->pluck('dosen_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($candidateIds->count() !== 1) {
            return null;
        }

        return $candidateIds->first();
    }

    private function resolveSinglePembimbingLapanganId(PengajuanMagang $pengajuan): ?int
    {
        $candidateIds = collect([$pengajuan->pembimbing_lapangan_id])
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($candidateIds->count() !== 1) {
            return null;
        }

        return $candidateIds->first();
    }


    private function logbookCutoffForKelayakan(PengajuanMagang $pengajuan): ?Carbon
    {
        if ($pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() ?? false) {
            return Carbon::parse('2026-06-20 23:59:00');
        }

        return null;
    }

    private function seminarEligibility(PengajuanMagang $pengajuan): array
    {
        $reasons = [];
        $isAngkatanKhusus = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() ?? false;
        if (!in_array($pengajuan->status_pengajuan, ['berjalan', 'selesai'], true)) $reasons[] = 'Pengajuan SK Magang belum berjalan/selesai.';
        if ($isAngkatanKhusus && !$pengajuan->penempatanLengkap()) $reasons[] = 'Data penempatan magang Anda belum lengkap. Silakan hubungi admin/fakultas untuk melengkapi dosen pembimbing, pembimbing lapangan, instansi, dan tanggal magang.';
        if (!$pengajuan->dokumens->where('jenis_dokumen', 'sk_magang')->where('status_verifikasi', 'valid')->whereNotNull('file_path')->count()) $reasons[] = 'SK Magang belum tersedia. Mahasiswa belum bisa mengajukan seminar.';
        if ($pengajuan->bimbingans->isEmpty()) $reasons[] = 'Dosen pembimbing belum ditugaskan.';
        if (!$pengajuan->pembimbing_lapangan_id) $reasons[] = 'Pembimbing lapangan belum terhubung.';
        if (!$pengajuan->kelayakanSeminar?->isApproved()) $reasons[] = 'Anda belum dapat mengajukan Seminar Magang karena laporan, output magang, produk magang, dan draft jurnal belum disetujui oleh dosen pembimbing dan pembimbing lapangan.';
        $missingWeeks = [];
        if ($pengajuan->mahasiswa?->absensiAktif()) {
            $missingAbsensi = $this->findMissingAbsensiDates($pengajuan);
            if (count($missingAbsensi)) $reasons[] = 'Absensi magang belum lengkap atau belum disetujui pada tanggal: ' . implode(', ', $missingAbsensi) . '.';
            if ($pengajuan->absensis->whereIn('status', ['pending', 'revisi', 'ditolak'])->count()) $reasons[] = 'Masih ada absensi magang pending/revisi/ditolak.';
        }
        if (in_array($pengajuan->status_seminar, ['menunggu_jadwal', 'terjadwal'], true)) $reasons[] = 'Masih ada pengajuan seminar aktif.';
        return ['allowed' => count($reasons) === 0, 'reasons' => $reasons, 'missing' => $missingWeeks];
    }

    private function findMissingWeeks(PengajuanMagang $pengajuan, $toDate = null): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        if (!$start) return [];
        $end = $toDate ? Carbon::parse($toDate) : ($pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now());
        if ($pengajuan->tanggal_selesai && $end->greaterThan(Carbon::parse($pengajuan->tanggal_selesai))) {
            $end = Carbon::parse($pengajuan->tanggal_selesai);
        }
        $filled = $pengajuan->logbooks->mapWithKeys(fn($l) => [Carbon::parse($l->tanggal)->startOfWeek()->toDateString() => true]);
        $missing = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addWeek()) if (!isset($filled[$cursor->toDateString()])) $missing[] = $cursor->toDateString();
        return $missing;
    }

    private function findMissingAbsensiDates(PengajuanMagang $pengajuan): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai) : null;
        if (!$start) return [];
        $end = $pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now();
        $approved = $pengajuan->absensis->where('status', 'disetujui')->mapWithKeys(fn($a) => [Carbon::parse($a->tanggal)->toDateString() => true]);
        $missing = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDay()) {
            if ($cursor->isWeekday() && !isset($approved[$cursor->toDateString()])) $missing[] = $cursor->toDateString();
        }
        return $missing;
    }

    private function sendKelayakanNotifications(KelayakanSeminar $kelayakan): void
    {
        try {
            $namaMahasiswa = $kelayakan->pengajuan?->mahasiswa?->nama_lengkap ?? 'Mahasiswa';

            if ($kelayakan->dosen?->user) {
                $this->notify(
                    $kelayakan->dosen->user->id,
                    'Kelayakan Seminar Baru',
                    'Mahasiswa ' . $namaMahasiswa . ' mengirim bahan kelayakan seminar.',
                    route('dosen.seminar.show', $kelayakan->id)
                );
            }

            if ($kelayakan->pembimbingLapangan?->user) {
                $this->notify(
                    $kelayakan->pembimbingLapangan->user->id,
                    'Kelayakan Seminar Baru',
                    'Mahasiswa ' . $namaMahasiswa . ' mengirim bahan kelayakan seminar.',
                    route('pembimbing.seminar.show', $kelayakan->id)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Notifikasi kelayakan seminar gagal dikirim', [
                'kelayakan_id' => $kelayakan->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
    private function notify($userId, $judul, $pesan, $targetUrl): void
    {
        Notifikasi::create(['user_id' => $userId, 'judul' => $judul, 'pesan' => $pesan, 'status' => 'belum', 'target_url' => $targetUrl]);
    }

    private function syncDokumenArsip(int $pengajuanId, string $jenis, string $path, string $catatan): void
    {
        Dokumen::updateOrCreate(
            ['pengajuan_id' => $pengajuanId, 'jenis_dokumen' => $jenis],
            [
                'file_path' => $path,
                'tanggal_upload' => now()->toDateString(),
                'status_verifikasi' => 'valid',
                'catatan' => $catatan,
            ]
        );
    }

    private function fileResponse(KelayakanSeminar $kelayakan, string $type, bool $download)
    {
        $path = match ($type) {
            'produk' => $kelayakan->produk_magang,
            'jurnal' => $kelayakan->draft_jurnal,
            default => $kelayakan->laporan_hasil_magang,
        };
        return $download ? $this->publicDownloadResponse($path) : $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }
}
