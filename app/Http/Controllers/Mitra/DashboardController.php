<?php
namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;

class DashboardController extends Controller
{
    public function index()
    {
        $mitraId    = auth()->user()->mitraUser?->mitra_id;
        $pengajuans = PengajuanMagang::where('mitra_id', $mitraId)->with('mahasiswa')->get();
        return view('mitra.dashboard', compact('pengajuans'));
    }
}