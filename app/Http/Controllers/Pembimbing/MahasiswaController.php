<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $pembimbing = auth()->user()->pembimbingLapangan;
        abort_unless($pembimbing, 403);

        $pengajuans = $this->baseQuery($pembimbing->id)
            ->when($request->search, fn($q, $s) => $q->whereHas('mahasiswa', fn($m) => $m
                ->where('nama_lengkap', 'like', "%{$s}%")
                ->orWhere('nim', 'like', "%{$s}%")))
            ->with(['mahasiswa.prodi', 'periode', 'mitra', 'bimbingans.dosen', 'penilaian'])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('pembimbing.mahasiswa.index', compact('pengajuans', 'pembimbing'));
    }

    public function show($pengajuanId)
    {
        $pembimbing = auth()->user()->pembimbingLapangan;
        abort_unless($pembimbing, 403);

        $pengajuan = $this->baseQuery($pembimbing->id)
            ->with(['mahasiswa.prodi', 'periode', 'bimbingans.dosen', 'mitra', 'pembimbingLapangan'])
            ->findOrFail($pengajuanId);

        return view('pembimbing.mahasiswa.show', compact('pengajuan', 'pembimbing'));
    }

    private function baseQuery(int $pembimbingId)
    {
        return PengajuanMagang::where('pembimbing_lapangan_id', $pembimbingId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);
    }
}