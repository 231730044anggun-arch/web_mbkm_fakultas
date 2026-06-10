<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = Mitra::query()->orderBy('nama_instansi');

        if ($request->filled('q')) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('nama_instansi', 'like', '%' . $request->q . '%')
                    ->orWhere('kota', 'like', '%' . $request->q . '%')
                    ->orWhere('bidang_industri', 'like', '%' . $request->q . '%');
            });
        }

        $mitras = $query->paginate(12)->withQueryString();

        return view('mahasiswa.mitra.index', compact('mitras'));
    }
}
