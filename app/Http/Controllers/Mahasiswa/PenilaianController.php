<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
use Illuminate\Support\Facades\Storage;

class PenilaianController extends Controller
{
    use HandlesSecurePublicFiles;
    public function show($pengajuanId)
    {
        $pengajuan = PengajuanMagang::with(['mahasiswa', 'penilaian'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);

        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->with('details')->first();
        $seminarValid = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif() || $pengajuan->status_seminar === 'selesai';

        return view('mahasiswa.penilaian.show', compact('pengajuan', 'penilaian', 'seminarValid'));
    }

    public function file($pengajuanId, string $type)
    {
        $pengajuan = PengajuanMagang::with('penilaian')
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);

        $path = $type === 'pembimbing'
            ? $pengajuan->penilaian?->file_penilaian_formal_pembimbing
            : $pengajuan->penilaian?->file_penilaian_formal_dosen;

        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }
}
