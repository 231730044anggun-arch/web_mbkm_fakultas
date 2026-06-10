<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::latest()->paginate(15);
        return view('admin.periode.index', compact('periodes'));
    }

    public function create()
    {
        return view('admin.periode.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_periode'    => 'required|string',
            'tahun'           => 'required|integer',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status'          => 'nullable|in:aktif,nonaktif',
        ]);

        DB::transaction(function () use ($data) {
            if (($data['status'] ?? 'nonaktif') === 'aktif') {
                Periode::where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            Periode::create($data);
        });

        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit(Periode $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, Periode $periode)
    {
        $data = $request->validate([
            'nama_periode'    => 'required|string',
            'tahun'           => 'required|integer',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:aktif,nonaktif',
        ]);

        DB::transaction(function () use ($periode, $data) {
            if ($data['status'] === 'aktif') {
                Periode::whereKeyNot($periode->id)->where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            $periode->update($data);
        });

        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        if ($periode->pengajuans()->exists()) {
            return redirect()->route('admin.periode.index')->with('error', 'Periode tidak bisa dihapus karena sudah dipakai pada pengajuan.');
        }

        $periode->delete();
        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil dihapus.');
    }

    public function activate(Periode $periode)
    {
        DB::transaction(function () use ($periode) {
            Periode::where('status', 'aktif')->update(['status' => 'nonaktif']);
            $periode->update(['status' => 'aktif']);
        });

        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil diaktifkan.');
    }
}
