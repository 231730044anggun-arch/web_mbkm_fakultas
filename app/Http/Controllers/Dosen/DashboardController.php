<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Logbook;

class DashboardController extends Controller
{
    public function index()
    {
        $dosenId        = auth()->user()->dosen?->id;
        $bimbingans     = Bimbingan::where('dosen_id', $dosenId)->with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.pembimbingLapangan'])->get();
        $pengajuanIds   = $bimbingans->pluck('pengajuan_id');
        $logbookPending = Logbook::whereIn('pengajuan_id', $pengajuanIds)->where('status_validasi', 'pending')->count();
        return view('dosen.dashboard', compact('bimbingans', 'logbookPending'));
    }
}
