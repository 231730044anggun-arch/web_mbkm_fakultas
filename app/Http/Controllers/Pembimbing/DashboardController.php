<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;

class DashboardController extends Controller
{
    public function index()
    {
        $pembimbing = auth()->user()->pembimbingLapangan;
        abort_unless($pembimbing, 403);

        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'periode', 'penilaian'])
            ->where('pembimbing_lapangan_id', $pembimbing->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest('updated_at')
            ->get();

        return view('pembimbing.dashboard', compact('pengajuans', 'pembimbing'));
    }
}