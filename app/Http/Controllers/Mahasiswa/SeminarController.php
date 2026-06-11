<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\KelayakanSeminar;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PDF;

class SeminarController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        if (!$mahasiswa) return view('mahasiswa.info-butuh-pengajuan', ['feature' => 'Seminar Magang']);

        $pengajuans = PengajuanMagang::with([
            'mitra', 'periode', 'dokumens', 'logbooks', 'absensis',
            'bimbingans.dosen.user', 'bimbinganFormals', 'pembimbingLapangan.user', 'kelayakanSeminar'
        ])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest()
            ->get();

        $eligibility = $pengajuans->mapWithKeys(fn($pengajuan) => [$pengajuan->id => $this->seminarEligibility($pengajuan)]);
        return view('mahasiswa.seminar.index', compact('pengajuans', 'eligibility'));
    }

    public function storeKelayakan(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        $request->validate([
            'laporan_hasil_magang' => 'required|file|mimes:pdf|max:10240',
            'output_magang' => 'required|string|min:20',
            'produk_magang' => 'nullable|file|mimes:pdf,zip,rar,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:20480',
            'catatan_mahasiswa' => 'nullable|string|max:1000',
        ]);

        $dosenId = $pengajuan->bimbingans->first()?->dosen_id;
        $old = $pengajuan->kelayakanSeminar;
        $laporanPath = $request->file('laporan_hasil_magang')->store('documents/kelayakan-seminar/laporan', 'public');
        $produkPath = $request->hasFile('produk_magang')
            ? $request->file('produk_magang')->store('documents/kelayakan-seminar/produk', 'public')
            : ($old?->produk_magang);

        if ($old) {
            if ($old->laporan_hasil_magang) Storage::disk('public')->delete($old->laporan_hasil_magang);
            if ($request->hasFile('produk_magang') && $old->produk_magang) Storage::disk('public')->delete($old->produk_magang);
        }

        $kelayakan = KelayakanSeminar::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'dosen_id' => $dosenId,
                'pembimbing_lapangan_id' => $pengajuan->pembimbing_lapangan_id,
                'laporan_hasil_magang' => $laporanPath,
                'output_magang' => $request->output_magang,
                'produk_magang' => $produkPath,
                'catatan_mahasiswa' => $request->catatan_mahasiswa,
                'status_persetujuan_dosen' => 'menunggu',
                'catatan_dosen' => null,
                'tanggal_persetujuan_dosen' => null,
                'status_persetujuan_pembimbing' => 'menunggu',
                'catatan_pembimbing' => null,
                'tanggal_persetujuan_pembimbing' => null,
            ]
        );

        if ($pengajuan->bimbingans->first()?->dosen?->user) {
            $this->notify($pengajuan->bimbingans->first()->dosen->user->id, 'Kelayakan Seminar Baru', 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim bahan kelayakan seminar.', route('dosen.seminar.show', $kelayakan->id));
        }
        if ($pengajuan->pembimbingLapangan?->user) {
            $this->notify($pengajuan->pembimbingLapangan->user->id, 'Kelayakan Seminar Baru', 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengirim bahan kelayakan seminar.', route('pembimbing.seminar.show', $kelayakan->id));
        }

        return redirect()->route('mahasiswa.seminar.index')->with('success', 'Bahan kelayakan seminar berhasil dikirim. Tunggu persetujuan dosen pembimbing dan pembimbing lapangan.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengajuan_id' => 'required|exists:pengajuan_magangs,id',
            'judul_laporan' => 'required|string|max:255',
            'laporan_seminar' => 'required|file|mimes:pdf|max:10240',
            'catatan' => 'nullable|string|max:500',
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
        abort_unless($kelayakan->mahasiswa_id === auth()->user()->mahasiswaProfile?->id, 403);
        return $this->fileResponse($kelayakan, $type, false);
    }

    public function downloadSurat($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        abort_unless(in_array($pengajuan->status_seminar, ['terjadwal', 'selesai'], true), 403);
        $dokumen = Dokumen::where('pengajuan_id', $pengajuan->id)->where('jenis_dokumen', 'surat_seminar')->where('status_verifikasi', 'valid')->latest()->first();
        if ($dokumen && Storage::disk('public')->exists($dokumen->file_path)) return Storage::disk('public')->download($dokumen->file_path);
        $html = view('surat.sk_seminar', compact('pengajuan'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $path = 'surat/sk_seminar_' . $pengajuan->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        Dokumen::updateOrCreate(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_seminar'], ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']);
        return Storage::disk('public')->download($path);
    }

    private function findOwnedPengajuan($pengajuanId): PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa.user', 'dokumens', 'logbooks', 'absensis', 'bimbingans.dosen.user', 'bimbinganFormals', 'mitra', 'pembimbingLapangan.user', 'kelayakanSeminar'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);
    }

    private function seminarEligibility(PengajuanMagang $pengajuan): array
    {
        $reasons = [];
        if (!in_array($pengajuan->status_pengajuan, ['berjalan', 'selesai'], true)) $reasons[] = 'Pengajuan SK Magang belum berjalan/selesai.';
        if (!$pengajuan->dokumens->where('jenis_dokumen', 'sk_magang')->where('status_verifikasi', 'valid')->whereNotNull('file_path')->count()) $reasons[] = 'SK Magang belum tersedia. Mahasiswa belum bisa mengajukan seminar.';
        if ($pengajuan->bimbingans->isEmpty()) $reasons[] = 'Dosen pembimbing belum ditugaskan.';
        if (!$pengajuan->pembimbing_lapangan_id) $reasons[] = 'Pembimbing lapangan belum terhubung.';
        if ($pengajuan->bimbinganFormals->isEmpty()) $reasons[] = 'Anda belum dapat mengajukan Seminar Magang karena belum memiliki riwayat bimbingan dengan dosen pembimbing.';
        if (!$pengajuan->kelayakanSeminar?->isApproved()) $reasons[] = 'Anda belum dapat mengajukan Seminar Magang karena laporan, output magang, dan produk magang belum disetujui oleh dosen pembimbing dan pembimbing lapangan.';
        $missingWeeks = $this->findMissingWeeks($pengajuan);
        if (count($missingWeeks)) $reasons[] = 'Logbook belum diisi pada minggu: ' . implode(', ', $missingWeeks) . '.';
        if ($pengajuan->logbooks->filter(fn($l) => ($l->status_dosen ?? 'pending') !== 'disetujui' || ($l->status_mitra ?? 'pending') !== 'disetujui')->count()) $reasons[] = 'Masih ada logbook yang belum disetujui dosen dan pembimbing lapangan.';
        if (config('mbkm.absensi_aktif')) {
            $missingAbsensi = $this->findMissingAbsensiDates($pengajuan);
            if (count($missingAbsensi)) $reasons[] = 'Absensi magang belum lengkap atau belum disetujui pada tanggal: ' . implode(', ', $missingAbsensi) . '.';
            if ($pengajuan->absensis->whereIn('status', ['pending', 'revisi', 'ditolak'])->count()) $reasons[] = 'Masih ada absensi magang pending/revisi/ditolak.';
        }
        if (in_array($pengajuan->status_seminar, ['menunggu_jadwal', 'terjadwal'], true)) $reasons[] = 'Masih ada pengajuan seminar aktif.';
        return ['allowed' => count($reasons) === 0, 'reasons' => $reasons, 'missing' => $missingWeeks];
    }

    private function findMissingWeeks(PengajuanMagang $pengajuan): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        if (!$start) return [];
        $end = $pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now();
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

    private function notify($userId, $judul, $pesan, $targetUrl): void
    {
        Notifikasi::create(['user_id' => $userId, 'judul' => $judul, 'pesan' => $pesan, 'status' => 'belum', 'target_url' => $targetUrl]);
    }

    private function fileResponse(KelayakanSeminar $kelayakan, string $type, bool $download)
    {
        $path = $type === 'produk' ? $kelayakan->produk_magang : $kelayakan->laporan_hasil_magang;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return $download ? Storage::disk('public')->download($path) : response()->file(Storage::disk('public')->path($path), ['Content-Disposition' => 'inline']);
    }
}