<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use App\Models\MahasiswaProfile;
use App\Models\Mitra;
use App\Models\Dosen;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_mahasiswa'    => MahasiswaProfile::count(),
            'total_mitra'        => Mitra::count(),
            'total_pengajuan'    => PengajuanMagang::count(),
            'total_dosen'        => Dosen::count(),
            'pengajuan_pending'  => PengajuanMagang::where('status_pengajuan', 'pending')->count(),
            'pengajuan_berjalan' => PengajuanMagang::where('status_pengajuan', 'berjalan')->count(),
            'pengajuan_selesai'  => PengajuanMagang::where('status_pengajuan', 'selesai')->count(),
        ];
        return view('admin.dashboard', $data);
    }
}