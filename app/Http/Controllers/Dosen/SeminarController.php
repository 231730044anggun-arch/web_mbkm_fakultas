<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;

class SeminarController extends Controller
{
    public function index()
    {
        $dosenId = auth()->user()->dosen?->id;
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode'])
            ->whereHas('bimbingans', fn($q) => $q->where('dosen_id', $dosenId))
            ->whereIn('status_seminar', ['menunggu', 'terjadwal', 'selesai', 'ditunda'])
            ->latest()
            ->paginate(15);

        return view('dosen.seminar.index', compact('pengajuans'));
    }
}
