<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Angkatan;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MahasiswaProfile;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Models\Mitra;
use App\Models\PembimbingLapangan;
use App\Models\Periode;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    private array $types = [
        'fakultas' => ['title' => 'Fakultas', 'model' => Fakultas::class, 'field' => 'nama_fakultas'],
        'program-studi' => ['title' => 'Program Studi', 'model' => Prodi::class, 'field' => 'nama_prodi'],
        'kelas' => ['title' => 'Kelas', 'model' => Kelas::class, 'field' => 'nama_kelas'],
        'angkatan' => ['title' => 'Angkatan', 'model' => Angkatan::class, 'field' => 'tahun'],
    ];

    public function index()
    {
        $cards = [
            ['label' => 'Fakultas', 'count' => Fakultas::count(), 'route' => route('admin.master.reference.index', 'fakultas')],
            ['label' => 'Program Studi', 'count' => Prodi::count(), 'route' => route('admin.master.reference.index', 'program-studi')],
            ['label' => 'Kelas', 'count' => Kelas::count(), 'route' => route('admin.master.reference.index', 'kelas')],
            ['label' => 'Angkatan', 'count' => Angkatan::count(), 'route' => route('admin.master.reference.index', 'angkatan')],
            ['label' => 'Periode Magang', 'count' => Periode::count(), 'route' => route('admin.periode.index')],
            ['label' => 'Data Mahasiswa', 'count' => MahasiswaProfile::count(), 'route' => route('admin.master.mahasiswa.index')],
            ['label' => 'Data Dosen', 'count' => Dosen::count(), 'route' => route('admin.master.dosen.index')],
            ['label' => 'Data Mitra/Instansi', 'count' => Mitra::count(), 'route' => route('admin.mitra.index')],
            ['label' => 'Pembimbing Lapangan', 'count' => PembimbingLapangan::count(), 'route' => route('admin.master.pembimbing.index')],
        ];

        return view('admin.master.index', compact('cards'));
    }

    public function reference(string $type)
    {
        $config = $this->config($type);
        $model = $config['model'];
        $field = $config['field'];
        $items = $model::orderBy($field)->paginate(20);
        $fakultas = Fakultas::orderBy('nama_fakultas')->get();

        return view('admin.master.reference', compact('type', 'config', 'field', 'items', 'fakultas'));
    }

    public function storeReference(Request $request, string $type)
    {
        $config = $this->config($type);
        $field = $config['field'];
        $rules = [$field => 'required|max:255'];
        if ($type === 'program-studi') $rules['fakultas_id'] = 'nullable|exists:fakultas,id';
        if (in_array($type, ['kelas', 'angkatan'], true)) $rules['status'] = 'required|in:aktif,nonaktif';
        $data = $request->validate($rules);

        $config['model']::updateOrCreate([$field => $data[$field]], $data);

        return back()->with('success', $config['title'] . ' berhasil disimpan.');
    }

    public function updateReference(Request $request, string $type, int $id)
    {
        $config = $this->config($type);
        $field = $config['field'];
        $rules = [$field => 'required|max:255'];
        if ($type === 'program-studi') $rules['fakultas_id'] = 'nullable|exists:fakultas,id';
        if (in_array($type, ['kelas', 'angkatan'], true)) $rules['status'] = 'required|in:aktif,nonaktif';
        $data = $request->validate($rules);

        $item = $config['model']::findOrFail($id);
        $item->update($data);

        return back()->with('success', $config['title'] . ' berhasil diperbarui.');
    }

    public function destroyReference(string $type, int $id)
    {
        $config = $this->config($type);
        $item = $config['model']::findOrFail($id);

        try {
            $item->delete();
            return back()->with('success', $config['title'] . ' berhasil dihapus.');
        } catch (QueryException $e) {
            if (isset($item->status)) {
                $item->update(['status' => 'nonaktif']);
                return back()->with('error', $config['title'] . ' sudah dipakai data lain, sehingga dinonaktifkan.');
            }
            return back()->with('error', $config['title'] . ' tidak dapat dihapus karena sudah dipakai data lain.');
        }
    }

    private function config(string $type): array
    {
        abort_unless(isset($this->types[$type]), 404);
        return $this->types[$type];
    }
}