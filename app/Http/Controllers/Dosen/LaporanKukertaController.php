<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\LaporanKukerta;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LaporanKukertaController extends Controller
{
    use HandlesSecurePublicFiles;
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
        $path = match ($type) {
            'laporan' => $laporan->laporan_kukerta,
            'foto' => ($laporan->foto_dokumentasi_kukerta[$index] ?? null),
            'output' => $laporan->output_kukerta_file,
            default => ($laporan->dokumentasi_kukerta[$index] ?? null),
        };
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function authorizeLaporan(LaporanKukerta $laporan): void
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless(Bimbingan::where('dosen_id', $dosenId)->where('pengajuan_id', $laporan->pengajuan_id)->exists(), 403);
    }
}
