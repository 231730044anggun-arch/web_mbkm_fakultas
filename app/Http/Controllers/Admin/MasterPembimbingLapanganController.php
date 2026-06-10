<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\PembimbingLapangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterPembimbingLapanganController extends Controller
{
    public function index(Request $request)
    {
        $pembimbings = PembimbingLapangan::with(['user', 'mitra'])
            ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('nama', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('no_hp', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.master.pembimbing.index', compact('pembimbings'));
    }

    public function create()
    {
        return view('admin.master.pembimbing.form', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->savePembimbing($data);
        return redirect()->route('admin.master.pembimbing.index')->with('success', 'Data pembimbing lapangan berhasil disimpan.');
    }

    public function show(PembimbingLapangan $pembimbing)
    {
        $pembimbing->load(['user', 'mitra']);
        return view('admin.master.pembimbing.show', compact('pembimbing'));
    }

    public function edit(PembimbingLapangan $pembimbing)
    {
        return view('admin.master.pembimbing.form', array_merge($this->formData(), compact('pembimbing')));
    }

    public function update(Request $request, PembimbingLapangan $pembimbing)
    {
        $data = $this->validated($request, $pembimbing);
        $this->savePembimbing($data, $pembimbing);
        return redirect()->route('admin.master.pembimbing.index')->with('success', 'Data pembimbing lapangan berhasil diperbarui.');
    }

    public function destroy(PembimbingLapangan $pembimbing)
    {
        if ($pembimbing->pengajuan_id) {
            $pembimbing->update(['status' => 'nonaktif']);
            return back()->with('error', 'Pembimbing sudah terhubung riwayat pengajuan. Data tidak dihapus dan status dinonaktifkan.');
        }
        $pembimbing->delete();
        return back()->with('success', 'Data pembimbing lapangan berhasil dihapus.');
    }

    private function validated(Request $request, ?PembimbingLapangan $pembimbing = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('pembimbing_lapangans', 'email')->ignore($pembimbing?->id)],
            'no_hp' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:150',
            'mitra_id' => 'required|exists:mitras,id',
            'status' => 'required|in:aktif,nonaktif',
            'catatan' => 'nullable|string',
            'buat_akun' => 'nullable|boolean',
            'password' => 'nullable|min:6',
        ]);
    }

    private function savePembimbing(array $data, ?PembimbingLapangan $pembimbing = null): PembimbingLapangan
    {
        $mitra = Mitra::findOrFail($data['mitra_id']);
        $userId = $pembimbing?->user_id;
        $shouldCreateAccount = ($data['buat_akun'] ?? false) || filled($data['password'] ?? null);

        $user = User::where('email', $data['email'])->first();
        if ($user && !in_array($user->role, ['pembimbing_lapangan', 'mitra'], true)) {
            throw new \RuntimeException('Email pembimbing sudah digunakan oleh role lain.');
        }
        if (!$user && $shouldCreateAccount) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?: '12345678'),
                'role' => 'pembimbing_lapangan',
                'status' => $data['status'],
            ]);
        }
        if ($user) {
            $user->update([
                'name' => $data['nama'],
                'status' => $data['status'],
            ]);
            if (filled($data['password'] ?? null)) {
                $user->update(['password' => Hash::make($data['password'])]);
            }
            $userId = $user->id;
        }

        $pembimbing = $pembimbing ?: PembimbingLapangan::firstOrNew(['email' => $data['email']]);
        $pembimbing->fill([
            'user_id' => $userId,
            'mitra_id' => $mitra->id,
            'nama' => $data['nama'],
            'jabatan' => $data['jabatan'] ?? null,
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'instansi' => $mitra->nama_instansi,
            'status' => $data['status'],
            'catatan' => $data['catatan'] ?? null,
        ]);
        $pembimbing->syncProfileStatus();
        $pembimbing->save();

        return $pembimbing;
    }

    private function perPage(Request $request): int
    {
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }
    private function formData(): array
    {
        return ['mitras' => Mitra::orderBy('nama_instansi')->get()];
    }
}