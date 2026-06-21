<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        $mahasiswaId = $mahasiswa?->id;
        $allPengajuans = PengajuanMagang::where('mahasiswa_id', $mahasiswaId)
            ->with(['periode', 'mitra', 'dokumens', 'bimbingans.dosen.user', 'pembimbingLapangan.user'])
            ->latest()
            ->get();
        $aktif = $allPengajuans->where('status_pengajuan', 'berjalan')->first();
        $informasiMagang = $mahasiswa?->pengajuanMagangAktif();
        $informasiMagang?->loadMissing(['periode', 'mitra', 'dokumens', 'bimbingans.dosen.user', 'pembimbingLapangan.user']);

        $pengajuans = $mahasiswa?->isAngkatanKhususSkKolektif()
            ? $allPengajuans->reject(fn($p) => $p->isPenempatanKolektif())
            : $allPengajuans;

        return view('mahasiswa.dashboard', compact('pengajuans', 'aktif', 'informasiMagang', 'mahasiswa'));
    }
}
