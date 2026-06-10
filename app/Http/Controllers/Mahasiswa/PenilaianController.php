<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;

class PenilaianController extends Controller
{
    public function show($pengajuanId)
    {
        $pengajuan = PengajuanMagang::with(['mahasiswa', 'penilaian'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);

        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->with('details')->first();
        $seminarValid = $pengajuan->hasValidSeminar();

        return view('mahasiswa.penilaian.show', compact('pengajuan', 'penilaian', 'seminarValid'));
    }
}
