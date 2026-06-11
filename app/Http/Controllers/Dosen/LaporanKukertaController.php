<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\LaporanKukerta;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LaporanKukertaController extends Controller
{
    public function index()
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless($dosenId, 403);

        $laporans = LaporanKukerta::with(['mahasiswa.prodi', 'pengajuan.mitra'])
            ->whereHas('pengajuan.bimbingans', fn($q) => $q->where('dosen_id', $dosenId))
            ->latest()
            ->paginate(15);

        return view('dosen.laporan-kukerta.index', compact('laporans'));
    }

    public function show(LaporanKukerta $laporan)
    {
        $this->authorizeLaporan($laporan);
        $laporan->load(['mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode']);

        return view('dosen.laporan-kukerta.show', compact('laporan'));
    }

    public function file(LaporanKukerta $laporan, string $type, ?int $index = null)
    {
        $this->authorizeLaporan($laporan);
        $path = $type === 'laporan'
            ? $laporan->laporan_kukerta
            : ($laporan->dokumentasi_kukerta[$index] ?? null);
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);
        return response()->stream(fn() => readfile($absolutePath), 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($path)),
        ]);
    }

    private function authorizeLaporan(LaporanKukerta $laporan): void
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless(Bimbingan::where('dosen_id', $dosenId)->where('pengajuan_id', $laporan->pengajuan_id)->exists(), 403);
    }
}