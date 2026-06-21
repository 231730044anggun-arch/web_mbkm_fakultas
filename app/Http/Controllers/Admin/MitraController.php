<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MitraController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index(Request $request)
    {
        $query = Mitra::query();

        if ($search = $request->query('search')) {
            $query->where('nama_instansi', 'like', "%{$search}%")
                ->orWhere('kota', 'like', "%{$search}%")
                ->orWhere('bidang_industri', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        if ($jenis = $request->query('jenis_mitra')) {
            $query->where('jenis_mitra', $jenis);
        }

        if ($status = $request->query('status_mitra_detail')) {
            $query->where('status_mitra_detail', $status);
        }

        if ($statusMou = $request->query('status_mou')) {
            $query->where('status_mou', $statusMou);
        }

        $mitras = $query->latest()->paginate(15)->withQueryString();
        return view('admin.mitra.index', compact('mitras'));
    }

    public function create()
    {
        return view('admin.mitra.create');
    }

    public function show(Mitra $mitra)
    {
        return view('admin.mitra.show', compact('mitra'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'jenis_mitra'   => 'nullable|in:ber_mou,non_mou',
            'status_mitra_detail' => 'nullable|in:menunggu_verifikasi,aktif,nonaktif',
            'status_mou'    => 'nullable|in:aktif,tidak,expired',
            'email'         => 'nullable|email|max:150',
            'tanggal_mulai_mou' => 'nullable|date',
            'tanggal_berakhir_mou' => 'nullable|date|after_or_equal:tanggal_mulai_mou',
            'file_mou' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = $request->except('file_mou');
        if ($request->hasFile('file_mou')) {
            $data['file_mou'] = $request->file('file_mou')->store('documents/mou', 'public');
        }

        Mitra::create(array_merge($data, [
            'status_mitra_detail' => $request->status_mitra_detail ?? 'aktif',
            'jenis_mitra' => $request->jenis_mitra ?? 'non_mou',
        ]));

        return redirect()->route('admin.mitra.index')->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function mou(Mitra $mitra)
    {
        return $this->publicInlineResponse($mitra->file_mou, 'MoU ' . ($mitra->nama_instansi ?? 'Mitra') . '.pdf');
    }
    public function edit(Mitra $mitra)
    {
        return view('admin.mitra.edit', compact('mitra'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'jenis_mitra'   => 'nullable|in:ber_mou,non_mou',
            'status_mitra_detail' => 'nullable|in:menunggu_verifikasi,aktif,nonaktif',
            'status_mou'    => 'nullable|in:aktif,tidak,expired',
            'email'         => 'nullable|email|max:150',
            'tanggal_mulai_mou' => 'nullable|date',
            'tanggal_berakhir_mou' => 'nullable|date|after_or_equal:tanggal_mulai_mou',
            'file_mou' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = $request->except('file_mou');
        if ($request->hasFile('file_mou')) {
            if ($mitra->file_mou) {
                $this->deletePublicFileIfExists($mitra->file_mou);
            }
            $data['file_mou'] = $request->file('file_mou')->store('documents/mou', 'public');
        }

        $mitra->update(array_merge($data, [
            'status_mitra_detail' => $request->status_mitra_detail ?? $mitra->status_mitra_detail,
            'jenis_mitra' => $request->jenis_mitra ?? $mitra->jenis_mitra,
        ]));

        return redirect()->route('admin.mitra.index')->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra)
    {
        if ($mitra->pengajuans()->exists() || $mitra->mitraUsers()->exists()) {
            $mitra->update(['status_mitra_detail' => 'nonaktif']);
            return redirect()->route('admin.mitra.index')->with('error', 'Mitra tidak dapat dihapus permanen karena sudah memiliki riwayat. Mitra telah dinonaktifkan.');
        }

        if ($mitra->file_mou) {
            $this->deletePublicFileIfExists($mitra->file_mou);
        }
        $mitra->delete();
        return redirect()->route('admin.mitra.index')->with('success', 'Mitra berhasil dihapus.');
    }
}
