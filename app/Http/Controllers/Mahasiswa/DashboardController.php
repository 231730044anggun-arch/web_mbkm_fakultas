<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswaId = auth()->user()->mahasiswaProfile?->id;
        $pengajuans  = PengajuanMagang::where('mahasiswa_id', $mahasiswaId)->with(['periode', 'mitra'])->latest()->get();
        $aktif       = $pengajuans->where('status_pengajuan', 'berjalan')->first();
        return view('mahasiswa.dashboard', compact('pengajuans', 'aktif'));
    }
}