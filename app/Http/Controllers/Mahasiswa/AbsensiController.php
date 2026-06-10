<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AbsensiMagang;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $this->activePengajuan();
        if (!$pengajuan) {
            return view('mahasiswa.info-butuh-pengajuan', [
                'feature' => 'Absensi Magang',
                'message' => 'Absensi Magang belum dapat digunakan karena SK Magang belum berstatus berjalan, mitra belum terhubung, atau tanggal magang belum lengkap.',
            ]);
        }

        $query = AbsensiMagang::where('pengajuan_magang_id', $pengajuan->id);
        if ($request->filled('from')) $query->where('tanggal', '>=', $request->from);
        if ($request->filled('to')) $query->where('tanggal', '<=', $request->to);
        if ($request->filled('status')) $query->where('status', $request->status);

        $absensis = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();
        $rekap = $this->rekapAbsensi($pengajuan);
        $missingDates = $this->missingWorkdays($pengajuan);

        return view('mahasiswa.absensi.index', compact('pengajuan', 'absensis', 'rekap', 'missingDates'));
    }

    public function store(Request $request)
    {
        $pengajuan = $this->activePengajuan();
        if (!$pengajuan) {
            return redirect()->route('mahasiswa.absensi.index')->with('error', 'Absensi belum dapat diisi karena SK Magang belum berjalan atau mitra belum terhubung.');
        }

        $request->validate([
            'tanggal' => 'required|date|after_or_equal:' . $pengajuan->tanggal_mulai . '|before_or_equal:' . $pengajuan->tanggal_selesai,
            'jam_masuk' => 'required',
            'jam_pulang' => 'nullable|after:jam_masuk',
            'keterangan' => 'nullable|string|max:500',
            'bukti_hadir' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'tanggal.after_or_equal' => 'Tanggal hadir tidak boleh sebelum tanggal mulai magang.',
            'tanggal.before_or_equal' => 'Tanggal hadir tidak boleh melewati tanggal selesai magang.',
            'bukti_hadir.required' => 'Bukti hadir wajib diupload.',
            'bukti_hadir.mimes' => 'Bukti hadir harus berupa JPG, PNG, atau PDF.',
        ]);

        $existing = AbsensiMagang::where('pengajuan_magang_id', $pengajuan->id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($existing && !in_array($existing->status, ['revisi', 'ditolak'], true)) {
            return redirect()->back()->withInput()->with('error', 'Absensi untuk tanggal tersebut sudah ada dan belum dapat diganti. Penggantian hanya bisa dilakukan jika status revisi atau ditolak.');
        }

        $path = $request->file('bukti_hadir')->store('documents/absensi', 'public');

        if ($existing) {
            if ($existing->bukti_hadir) {
                Storage::disk('public')->delete($existing->bukti_hadir);
            }

            $existing->update([
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'keterangan' => $request->keterangan,
                'bukti_hadir' => $path,
                'status' => 'pending',
                'catatan_mitra' => null,
                'validated_by' => null,
                'validated_at' => null,
            ]);
            $absensi = $existing->fresh();
        } else {
            $absensi = AbsensiMagang::create([
                'pengajuan_magang_id' => $pengajuan->id,
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'mitra_id' => $pengajuan->mitra_id,
                'tanggal' => $request->tanggal,
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'keterangan' => $request->keterangan,
                'bukti_hadir' => $path,
                'status' => 'pending',
            ]);
        }

        $pengajuan->loadMissing('mahasiswa', 'mitra.mitraUsers.user');
        foreach (($pengajuan->mitra?->mitraUsers ?? collect()) as $mitraUser) {
            if ($mitraUser->user) {
                Notifikasi::create([
                    'user_id' => $mitraUser->user->id,
                    'judul' => 'Absensi Magang Baru',
                    'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' menginput absensi tanggal ' . $absensi->tanggal->format('Y-m-d') . '.',
                    'status' => 'belum',
                    'target_url' => route('mitra.absensi.index'),
                ]);
            }
        }

        return redirect()->route('mahasiswa.absensi.index')->with('success', 'Absensi berhasil dikirim dan menunggu validasi mitra.');
    }

    public function preview(AbsensiMagang $absensi)
    {
        $pengajuan = $this->activeOrOwnedPengajuan($absensi->pengajuan_magang_id);
        abort_unless($absensi->mahasiswa_id === auth()->user()->mahasiswaProfile?->id && $pengajuan, 403);
        abort_if(!$absensi->bukti_hadir || !Storage::disk('public')->exists($absensi->bukti_hadir), 404);

        $extension = pathinfo($absensi->bukti_hadir, PATHINFO_EXTENSION) ?: 'pdf';
        return $this->inlineFile($absensi->bukti_hadir, 'Bukti Absensi ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa') . '.' . $extension);
    }

    private function activePengajuan(): ?PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa', 'mitra.mitraUsers.user', 'absensis'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->where('status_pengajuan', 'berjalan')
            ->whereNotNull('mitra_id')
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_selesai')
            ->latest('updated_at')
            ->first();
    }

    private function activeOrOwnedPengajuan($pengajuanId): ?PengajuanMagang
    {
        return PengajuanMagang::with(['mahasiswa', 'mitra', 'absensis'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->find($pengajuanId);
    }

    private function rekapAbsensi(PengajuanMagang $pengajuan): array
    {
        $total = count($this->workdays($pengajuan, false));
        $absensis = $pengajuan->absensis;
        $approved = $absensis->where('status', 'disetujui')->count();

        return [
            'total_hari_wajib' => $total,
            'jumlah_absensi_masuk' => $absensis->count(),
            'jumlah_disetujui' => $approved,
            'jumlah_pending' => $absensis->where('status', 'pending')->count(),
            'jumlah_revisi' => $absensis->whereIn('status', ['revisi', 'ditolak'])->count(),
            'persentase_kehadiran' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ];
    }

    private function missingWorkdays(PengajuanMagang $pengajuan): array
    {
        $approvedDates = $pengajuan->absensis
            ->where('status', 'disetujui')
            ->map(fn($absensi) => $absensi->tanggal->format('Y-m-d'))
            ->flip();

        return array_values(array_filter($this->workdays($pengajuan, true), fn($date) => !isset($approvedDates[$date])));
    }

    private function workdays(PengajuanMagang $pengajuan, bool $capToday): array
    {
        if (!$pengajuan->tanggal_mulai || !$pengajuan->tanggal_selesai) return [];

        $start = Carbon::parse($pengajuan->tanggal_mulai);
        $end = Carbon::parse($pengajuan->tanggal_selesai);
        if ($capToday && Carbon::now()->lessThan($end)) {
            $end = Carbon::now();
        }

        $dates = [];
        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    private function inlineFile(string $path, string $filename)
    {
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(function () use ($absolutePath) {
            readfile($absolutePath);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($absolutePath),
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

