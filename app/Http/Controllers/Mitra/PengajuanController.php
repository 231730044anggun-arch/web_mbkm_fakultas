<?php
namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $mitraId    = auth()->user()->mitraUser?->mitra_id;
        $baseQuery = PengajuanMagang::where('mitra_id', $mitraId)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);

        $pics = (clone $baseQuery)
            ->whereNotNull('pic_nama')
            ->pluck('pic_nama')
            ->filter()
            ->unique()
            ->values();

        $pengajuans = $baseQuery
            ->when($request->filled('pic'), fn($q) => $q->where('pic_nama', $request->pic))
            ->with(['mahasiswa.prodi', 'periode', 'mitra', 'bimbingans.dosen', 'penilaian'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('mitra.pengajuan.index', compact('pengajuans', 'pics'));
    }

    public function show($pengajuanId)
    {
        $pengajuan = $this->queryForMitra()
            ->with(['mahasiswa.prodi', 'periode', 'bimbingans.dosen', 'mitra'])
            ->findOrFail($pengajuanId);

        return view('mitra.pengajuan.show', compact('pengajuan'));
    }

    public function pic(Request $request)
    {
        $query = $this->queryForMitra()->with(['mahasiswa.prodi']);
        $pengajuans = $query->get();
        $picGroups = $pengajuans
            ->filter(fn($p) => filled($p->pic_nama))
            ->groupBy('pic_nama');

        return view('mitra.pic.index', compact('picGroups'));
    }

    private function queryForMitra()
    {
        return PengajuanMagang::where('mitra_id', auth()->user()->mitraUser?->mitra_id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);
    }
}
