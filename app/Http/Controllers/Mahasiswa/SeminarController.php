<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
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
        if (!$mahasiswa) {
            return view('mahasiswa.info-butuh-pengajuan', ['feature' => 'Seminar Magang']);
        }

        $pengajuans = PengajuanMagang::with(['mitra', 'periode', 'dokumens', 'logbooks', 'absensis', 'bimbingans.dosen'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status_pengajuan', ['disetujui', 'berjalan', 'selesai'])
            ->latest()
            ->get();

        $eligibility = $pengajuans->mapWithKeys(fn($pengajuan) => [$pengajuan->id => $this->seminarEligibility($pengajuan)]);

        return view('mahasiswa.seminar.index', compact('pengajuans', 'eligibility'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengajuan_id' => 'required|exists:pengajuan_magangs,id',
            'judul_laporan' => 'required|string|max:255',
            'ringkasan_seminar' => 'nullable|string|max:1000',
            'usulan_tanggal_seminar' => 'nullable|date',
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pengajuan = $this->findOwnedPengajuan($request->pengajuan_id);
        if ($request->hasFile('file_laporan')) {
            Dokumen::create([
                'pengajuan_id' => $pengajuan->id,
                'jenis_dokumen' => 'laporan',
                'file_path' => $request->file('file_laporan')->store('documents/laporan', 'public'),
                'tanggal_upload' => now()->toDateString(),
                'status_verifikasi' => 'pending',
            ]);
            $pengajuan->update(['status_laporan' => 'pending']);
            $pengajuan->load('dokumens');
        }

        $eligibility = $this->seminarEligibility($pengajuan);

        if (!$eligibility['allowed']) {
            return redirect()->back()->with('error', implode(' ', $eligibility['reasons']))->withInput();
        }

        $pengajuan->update([
            'judul_laporan' => $request->judul_laporan,
            'ringkasan_seminar' => $request->ringkasan_seminar,
            'usulan_tanggal_seminar' => $request->usulan_tanggal_seminar,
            'status_seminar' => 'menunggu',
            'catatan_mahasiswa' => $request->catatan,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_diajukan',
            'keterangan' => $request->catatan ?: 'Mahasiswa mengajukan seminar magang.',
            'updated_by' => auth()->id(),
        ]);

        foreach (User::whereIn('role', ['admin', 'superadmin'])->get() as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul' => 'Pengajuan Seminar Magang',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengajukan seminar magang.',
                'status' => 'belum',
                'target_url' => route('admin.seminar.index'),
            ]);
        }

        return redirect()->route('mahasiswa.seminar.index')->with('success', 'Pengajuan seminar berhasil dikirim.');
    }

    public function cancel($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);

        if ($pengajuan->status_seminar !== 'menunggu' || $pengajuan->seminar_tanggal || $pengajuan->penilaian()->exists()) {
            return redirect()->back()->with('error', 'Seminar tidak dapat dibatalkan karena sudah dijadwalkan/selesai atau nilai sudah masuk.');
        }

        $pengajuan->update([
            'status_seminar' => 'dibatalkan',
            'catatan_mahasiswa' => trim(($pengajuan->catatan_mahasiswa ? $pengajuan->catatan_mahasiswa . "\n" : '') . 'Seminar dibatalkan oleh mahasiswa pada ' . now()->format('d M Y H:i')),
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_dibatalkan',
            'keterangan' => 'Pengajuan seminar dibatalkan oleh mahasiswa.',
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('mahasiswa.seminar.index')->with('success', 'Pengajuan seminar berhasil dibatalkan.');
    }
    public function downloadSurat($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        abort_if($pengajuan->status_seminar !== 'terjadwal' && $pengajuan->status_seminar !== 'selesai', 403);

        $dokumen = Dokumen::where('pengajuan_id', $pengajuan->id)
            ->where('jenis_dokumen', 'surat_seminar')
            ->where('status_verifikasi', 'valid')
            ->latest()
            ->first();

        if ($dokumen && Storage::disk('public')->exists(str_replace('storage/', '', $dokumen->file_path))) {
            return Storage::disk('public')->download(str_replace('storage/', '', $dokumen->file_path));
        }

        $html = view('surat.sk_seminar', compact('pengajuan'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $path = 'surat/sk_seminar_' . $pengajuan->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Dokumen::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_seminar'],
            ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
        );

        return Storage::disk('public')->download($path);
    }

    private function findOwnedPengajuan($pengajuanId): PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa.user', 'dokumens', 'logbooks', 'absensis', 'bimbingans.dosen', 'mitra'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);
    }

    private function seminarEligibility(PengajuanMagang $pengajuan): array
    {
        $reasons = [];

        if (!in_array($pengajuan->status_pengajuan, ['disetujui', 'berjalan', 'selesai'], true)) {
            $reasons[] = 'Pengajuan magang belum disetujui/berjalan/selesai.';
        }

        $hasValidReport = $pengajuan->status_laporan === 'valid'
            || $pengajuan->dokumens->where('jenis_dokumen', 'laporan')->where('status_verifikasi', 'valid')->isNotEmpty();
        if (!$hasValidReport) {
            $reasons[] = 'Laporan magang belum diupload atau belum valid.';
        }

        $hasSkMagang = $pengajuan->dokumens
            ->where('jenis_dokumen', 'sk_magang')
            ->where('status_verifikasi', 'valid')
            ->whereNotNull('file_path')
            ->isNotEmpty();
        if (!$hasSkMagang) {
            $reasons[] = 'SK Magang belum tersedia. Mahasiswa belum bisa mengajukan seminar.';
        }

        if ($pengajuan->bimbingans->isEmpty()) {
            $reasons[] = 'Dosen pembimbing belum ditugaskan.';
        }

        $missingWeeks = $this->findMissingWeeks($pengajuan);
        if (count($missingWeeks)) {
            $reasons[] = 'Logbook belum diisi pada minggu: ' . implode(', ', $missingWeeks) . '.';
        }

        $unapprovedLogbooks = $pengajuan->logbooks
            ->filter(fn($logbook) => ($logbook->status_dosen ?? 'pending') !== 'disetujui' || ($logbook->status_mitra ?? 'pending') !== 'disetujui')
            ->count();
        if ($unapprovedLogbooks > 0) {
            $reasons[] = 'Masih ada logbook yang belum disetujui dosen dan mitra.';
        }

        $missingAbsensi = $this->findMissingAbsensiDates($pengajuan);
        if (count($missingAbsensi)) {
            $reasons[] = 'Absensi magang belum lengkap atau belum disetujui mitra pada tanggal: ' . implode(', ', $missingAbsensi) . '.';
        }

        $invalidAbsensi = $pengajuan->absensis
            ->whereIn('status', ['pending', 'revisi', 'ditolak'])
            ->count();
        if ($invalidAbsensi > 0) {
            $reasons[] = 'Masih ada absensi magang pending/revisi/ditolak.';
        }

        $activeSeminar = $pengajuan->mahasiswa?->pengajuans()
            ->where('id', '!=', $pengajuan->id)
            ->whereIn('status_seminar', ['menunggu', 'terjadwal'])
            ->exists();
        if ($activeSeminar || in_array($pengajuan->status_seminar, ['menunggu', 'terjadwal'], true)) {
            $reasons[] = 'Masih ada pengajuan seminar aktif.';
        }

        return ['allowed' => count($reasons) === 0, 'reasons' => $reasons, 'missing' => $missingWeeks];
    }

    private function findMissingWeeks(PengajuanMagang $pengajuan): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        if (!$start) return [];

        $end = $pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now();
        $filledWeeks = $pengajuan->logbooks
            ->mapWithKeys(fn($logbook) => [Carbon::parse($logbook->tanggal)->startOfWeek()->toDateString() => true]);

        $missing = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addWeek()) {
            $week = $cursor->toDateString();
            if (!isset($filledWeeks[$week])) {
                $missing[] = $week;
            }
        }

        return $missing;
    }
    private function findMissingAbsensiDates(PengajuanMagang $pengajuan): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai) : null;
        if (!$start) return [];

        $end = $pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now();
        $approvedDates = $pengajuan->absensis
            ->where('status', 'disetujui')
            ->mapWithKeys(fn($absensi) => [Carbon::parse($absensi->tanggal)->toDateString() => true]);

        $missing = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDay()) {
            if (!$cursor->isWeekday()) {
                continue;
            }

            $date = $cursor->toDateString();
            if (!isset($approvedDates[$date])) {
                $missing[] = $date;
            }
        }

        return $missing;
    }
}

