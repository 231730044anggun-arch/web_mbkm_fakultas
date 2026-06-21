<?php
namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
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
    use HandlesSecurePublicFiles;
    public function index(Request $request)
    {
        $mitraId = auth()->user()->mitraUser?->mitra_id;
        abort_unless($mitraId, 403);

        $query = AbsensiMagang::with(['mahasiswa.prodi', 'pengajuan.periode', 'mitra'])
            ->where('mitra_id', $mitraId)
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']));

        if ($request->filled('mahasiswa_id')) $query->where('mahasiswa_id', $request->mahasiswa_id);
        if ($request->filled('from')) $query->where('tanggal', '>=', $request->from);
        if ($request->filled('to')) $query->where('tanggal', '<=', $request->to);
        if ($request->filled('status')) $query->where('status', $request->status);

        $absensis = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();
        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'absensis'])
            ->where('mitra_id', $mitraId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->orderByDesc('updated_at')
            ->get();

        $rekaps = $pengajuans->mapWithKeys(fn($pengajuan) => [$pengajuan->id => $this->rekapAbsensi($pengajuan)]);

        return view('mitra.absensi.index', compact('absensis', 'pengajuans', 'rekaps'));
    }

    public function validasi(Request $request, AbsensiMagang $absensi)
    {
        $this->authorizeAbsensi($absensi);

        $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan_mitra' => 'required_if:status,revisi,ditolak|nullable|string|max:500',
        ], [
            'catatan_mitra.required_if' => 'Catatan wajib diisi jika absensi dipilih revisi atau ditolak.',
        ]);

        $absensi->update([
            'status' => $request->status,
            'catatan_mitra' => $request->catatan_mitra,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        $absensi->loadMissing('mahasiswa.user', 'pengajuan');
        if ($absensi->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $absensi->mahasiswa->user->id,
                'judul' => $request->status === 'disetujui' ? 'Absensi Disetujui Mitra' : 'Absensi Perlu Diperbaiki',
                'pesan' => 'Absensi tanggal ' . $absensi->tanggal->format('Y-m-d') . ' diberi status: ' . $request->status . '. Catatan: ' . ($request->catatan_mitra ?? '-'),
                'status' => 'belum',
                'target_url' => route('mahasiswa.absensi.index'),
            ]);
        }

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    public function preview(AbsensiMagang $absensi)
    {
        $this->authorizeAbsensi($absensi);
        $extension = pathinfo($this->normalizePublicPath($absensi->bukti_hadir) ?: $absensi->bukti_hadir, PATHINFO_EXTENSION) ?: 'pdf'; return $this->publicInlineResponse($absensi->bukti_hadir, 'Bukti Absensi ' . ($absensi->mahasiswa->nama_lengkap ?? 'Mahasiswa') . '.' . $extension);
    }

    private function authorizeAbsensi(AbsensiMagang $absensi): void
    {
        abort_unless($this->idsMatch($absensi->mitra_id, auth()->user()->mitraUser?->mitra_id), 403);
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
