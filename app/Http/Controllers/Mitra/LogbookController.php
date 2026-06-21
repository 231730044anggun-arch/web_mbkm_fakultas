<?php
namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LogbookController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index(Request $request)
    {
        $query = $this->logbookQueryForMitra();
        $this->applyFilters($query, $request, 'status_mitra');

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
        $pengajuan = null;
        $missing = [];

        return view('mitra.logbook.index', compact('pengajuan', 'logbooks', 'missing'));
    }

    public function show(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForMitra($pengajuanId);
        $query = $this->logbookQueryForMitra()->where('pengajuan_id', $pengajuan->id);
        $this->applyFilters($query, $request, 'status_mitra');

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $missing = $this->findMissingWeeks($pengajuan);

        return view('mitra.logbook.index', compact('pengajuan', 'logbooks', 'missing'));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,revisi',
            'catatan' => 'required_if:status,revisi|nullable|string|max:500',
        ], [
            'catatan.required_if' => 'Catatan revisi wajib diisi jika status logbook dipilih revisi.',
        ]);

        $logbook = Logbook::with('pengajuan.mahasiswa.user')->findOrFail($id);
        $this->findPengajuanForMitra($logbook->pengajuan_id);

        $logbook->status_mitra = $request->status;
        $logbook->catatan_mitra = $request->catatan;
        $logbook->status_validasi = $this->combinedStatus($logbook);
        $logbook->save();

        $pengajuan = $logbook->pengajuan;
        if ($pengajuan?->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => $request->status === 'revisi' ? 'Revisi Logbook dari Mitra' : 'Logbook Disetujui Mitra',
                'pesan' => 'Logbook tanggal ' . $logbook->tanggal . ' diberi status mitra: ' . $request->status . '. Catatan: ' . ($request->catatan ?? '-'),
                'status' => 'belum',
                'target_url' => route('mahasiswa.logbook.index', $pengajuan->id),
            ]);
        }

        return redirect()->back()->with('success', 'Status validasi mitra berhasil diperbarui.');
    }

    public function previewFoto(Logbook $logbook)
    {
        $pengajuan = $this->findPengajuanForMitra($logbook->pengajuan_id);
        abort_unless($this->idsMatch($logbook->pengajuan_id, $pengajuan->id), 403);
        $extension = pathinfo($this->normalizePublicPath($logbook->bukti_foto) ?: $logbook->bukti_foto, PATHINFO_EXTENSION) ?: 'jpg'; return $this->publicInlineResponse($logbook->bukti_foto, 'Bukti Logbook ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa') . '.' . $extension);
    }

    private function logbookQueryForMitra()
    {
        $mitraId = auth()->user()->mitraUser?->mitra_id;
        abort_unless($mitraId, 403);

        return Logbook::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode', 'pengajuan.bimbingans.dosen'])
            ->whereHas('pengajuan', fn($query) => $query
                ->where('mitra_id', $mitraId)
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']));
    }

    private function applyFilters($query, Request $request, string $statusColumn): void
    {
        if ($request->filled('from')) {
            $query->where('tanggal', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('tanggal', '<=', $request->to);
        }

        if ($request->filled('status')) {
            $query->where($statusColumn, $request->status);
        }
    }

    private function findPengajuanForMitra($pengajuanId): PengajuanMagang
    {
        $mitraId = auth()->user()->mitraUser?->mitra_id;

        return PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'logbooks', 'periode', 'bimbingans.dosen'])
            ->where('mitra_id', $mitraId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->findOrFail($pengajuanId);
    }

    private function findMissingWeeks(PengajuanMagang $pengajuan): array
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        if (!$start) return [];

        $end = $pengajuan->tanggal_selesai ? Carbon::parse($pengajuan->tanggal_selesai) : Carbon::now();
        $filledWeeks = $pengajuan->logbooks()
            ->get()
            ->filter(fn($logbook) => ($logbook->status_dosen ?? 'pending') === 'disetujui' && ($logbook->status_mitra ?? 'pending') === 'disetujui')
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

    private function combinedStatus(Logbook $logbook): string
    {
        $dosen = $logbook->status_dosen ?: 'pending';
        $mitra = $logbook->status_mitra ?: 'pending';

        if ($dosen === 'revisi' || $mitra === 'revisi') {
            return 'revisi';
        }

        if ($dosen === 'disetujui' && $mitra === 'disetujui') {
            return 'disetujui';
        }

        return 'pending';
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
