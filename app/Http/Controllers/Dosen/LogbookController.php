<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Logbook;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use PDF;
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
        $dosenId = auth()->user()->dosen?->id;
        abort_unless($dosenId, 403);

        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'periode'])
            ->withCount([
                'logbooks',
                'logbooks as logbooks_disetujui_count' => fn($query) => $query
                    ->where('status_dosen', 'disetujui')
                    ->where('status_mitra', 'disetujui'),
            ])
            ->whereHas('bimbingans', fn($query) => $query->where('dosen_id', $dosenId))
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $pengajuan = null;
        $logbooks = collect();
        $missing = [];

        return view('dosen.logbook.index', compact('pengajuan', 'pengajuans', 'logbooks', 'missing'));
    }

    public function show(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);
        $query = $this->logbookQueryForDosen()->where('pengajuan_id', $pengajuan->id);
        $this->applyFilters($query, $request, 'status_dosen');

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $missing = $this->findMissingWeeks($pengajuan);

        return view('dosen.logbook.index', compact('pengajuan', 'logbooks', 'missing'));
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
        $this->findPengajuanForDosen($logbook->pengajuan_id);

        $logbook->status_dosen = $request->status;
        $logbook->catatan_dosen = $request->catatan;
        $logbook->status_validasi = $this->combinedStatus($logbook);
        $logbook->save();

        $pengajuan = $logbook->pengajuan;
        if ($pengajuan?->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => $request->status === 'revisi' ? 'Revisi Logbook dari Dosen' : 'Logbook Disetujui Dosen',
                'pesan' => 'Logbook tanggal ' . $logbook->tanggal . ' diberi status dosen: ' . $request->status . '. Catatan: ' . ($request->catatan ?? '-'),
                'status' => 'belum',
                'target_url' => route('mahasiswa.logbook.index', $pengajuan->id),
            ]);
        }

        return redirect()->back()->with('success', 'Status validasi dosen berhasil diperbarui.');
    }

    public function previewFoto(Logbook $logbook)
    {
        $pengajuan = $this->findPengajuanForDosen($logbook->pengajuan_id);
        abort_unless($this->idsMatch($logbook->pengajuan_id, $pengajuan->id), 403);
        $extension = pathinfo($this->normalizePublicPath($logbook->bukti_foto) ?: $logbook->bukti_foto, PATHINFO_EXTENSION) ?: 'jpg'; return $this->publicInlineResponse($logbook->bukti_foto, 'Bukti Logbook ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa') . '.' . $extension);
    }

    public function export(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);
        $query = Logbook::where('pengajuan_id', $pengajuan->id);

        $this->applyFilters($query, $request, 'status_dosen');

        $logbooks = $query->orderBy('tanggal')->get();
        $missing = $this->findMissingWeeks($pengajuan, $logbooks, $request->to ?: now()->toDateString());

        $html = view('logbook.pdf', compact('pengajuan', 'logbooks', 'missing'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download('logbook_pengajuan_' . $pengajuan->id . '.pdf');
    }

    private function logbookQueryForDosen()
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless($dosenId, 403);

        return Logbook::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode', 'pengajuan.bimbingans.dosen'])
            ->whereHas('pengajuan.bimbingans', fn($query) => $query->where('dosen_id', $dosenId))
            ->whereHas('pengajuan', fn($query) => $query
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

    private function findMissingWeeks(PengajuanMagang $pengajuan, $logbooks = null, $toDate = null)
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        $end = $toDate ? Carbon::parse($toDate) : Carbon::now();
        if ($pengajuan->tanggal_selesai) $end = Carbon::parse($pengajuan->tanggal_selesai);
        if (!$start) return [];

        $weeks = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addWeek()) {
            $weeks[] = $cursor->toDateString();
        }

        $entries = $logbooks ?? $pengajuan->logbooks()->get();
        $filledWeeks = [];
        foreach ($entries as $entry) {
            if (($entry->status_dosen ?? 'pending') === 'disetujui' && ($entry->status_mitra ?? 'pending') === 'disetujui') {
                $filledWeeks[Carbon::parse($entry->tanggal)->startOfWeek()->toDateString()] = true;
            }
        }

        return array_values(array_filter($weeks, fn($week) => !isset($filledWeeks[$week])));
    }

    private function findPengajuanForDosen($pengajuanId): PengajuanMagang
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless(Bimbingan::where('dosen_id', $dosenId)
            ->where('pengajuan_id', $pengajuanId)
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']))
            ->exists(), 403);

        return PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'logbooks', 'periode', 'bimbingans.dosen'])->findOrFail($pengajuanId);
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
