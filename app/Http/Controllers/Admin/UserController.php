<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\MahasiswaProfile;
use App\Models\Mitra;
use App\Models\MitraUser;
use App\Models\PembimbingLapangan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private array $roles = ['superadmin', 'admin', 'mahasiswa', 'dosen', 'mitra', 'pembimbing_lapangan'];

    public function index(Request $request)
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->when($request->role, fn($q, $role) => $q->where('role', $role))
            ->when($request->search, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $mitras = Mitra::orderBy('nama_instansi')->get();
        return view('admin.users.create', ['roles' => $this->roles, 'mitras' => $mitras]);
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        if (($data['role'] ?? null) === 'mahasiswa' && MahasiswaProfile::where('nim', $request->nim)->whereNotNull('user_id')->exists()) {
            return back()->withErrors(['nim' => 'NIM sudah terhubung dengan akun lain.'])->withInput();
        }
        if (($data['role'] ?? null) === 'dosen' && Dosen::where('nidn', $request->nidn)->whereNotNull('user_id')->exists()) {
            return back()->withErrors(['nidn' => 'NIDN/NIP sudah terhubung dengan akun lain.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => 'aktif',
        ]);

        $this->linkRoleMaster($request, $user);

        return redirect()->route('admin.users.index')->with('success', 'Akun user berhasil dibuat dan dihubungkan ke master data jika diperlukan.');
    }

    public function edit(User $user)
    {
        $user->load(['mahasiswaProfile', 'dosen', 'mitraUser.mitra', 'pembimbingLapangan.mitra']);
        $mitras = Mitra::orderBy('nama_instansi')->get();
        return view('admin.users.edit', ['user' => $user, 'roles' => $this->roles, 'mitras' => $mitras]);
    }

    public function show(User $user)
    {
        $user->load(['mahasiswaProfile.fakultas', 'mahasiswaProfile.prodi', 'mahasiswaProfile.kelasMaster', 'mahasiswaProfile.angkatanMaster', 'dosen.prodi', 'mitraUser.mitra', 'pembimbingLapangan.mitra']);
        return view('admin.users.show', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $this->linkRoleMaster($request, $user);

        return redirect()->route('admin.users.show', $user)->with('success', 'Akun user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Akun yang sedang digunakan tidak bisa dinonaktifkan atau dihapus dari halaman ini.');
        }

        if (request('action') === 'delete') {
            if ($this->hasHistoricalData($user)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User tidak bisa dihapus karena sudah memiliki data terkait. Gunakan Nonaktifkan agar data historis tetap aman.');
            }

            try {
                $user->mahasiswaProfile?->update(['user_id' => null]);
                $user->dosen?->update(['user_id' => null]);
                $user->pembimbingLapangan?->update(['user_id' => null]);
                $user->mitraUser?->delete();
                $user->notifikasis()->delete();
                $user->delete();
                return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
            } catch (QueryException $e) {
                return redirect()->route('admin.users.index')->with('error', 'User tidak dapat dihapus karena masih dipakai data lain. Gunakan Nonaktifkan.');
            }
        }

        $user->update(['status' => 'nonaktif']);
        $user->dosen?->update(['status_dosen' => 'nonaktif']);
        $user->pembimbingLapangan?->update(['status' => 'nonaktif']);

        return redirect()->route('admin.users.show', $user)->with('success', 'User berhasil dinonaktifkan tanpa menghapus data historis.');
    }

    private function perPage(Request $request): int
    {
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }
    private function validateUser(Request $request, ?User $user = null): array
    {
        $roleRules = implode(',', $this->roles);
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'min:6'],
            'role' => "required|in:{$roleRules}",
            'status' => $user ? 'required|in:aktif,nonaktif' : 'nullable',
            'nim' => 'required_if:role,mahasiswa|nullable|string|max:50',
            'nama_lengkap' => 'required_if:role,mahasiswa|nullable|string|max:255',
            'nidn' => 'required_if:role,dosen|nullable|string|max:80',
            'nama_dosen' => 'required_if:role,dosen|nullable|string|max:255',
            'nama_pembimbing' => 'required_if:role,pembimbing_lapangan|nullable|string|max:150',
            'no_hp_pembimbing' => 'nullable|string|max:100',
            'jabatan_pembimbing' => 'nullable|string|max:150',
            'mitra_id' => 'required_if:role,pembimbing_lapangan|nullable|exists:mitras,id',
            'jabatan_mitra' => 'nullable|string|max:150',
        ];

        return $request->validate($rules);
    }

    private function linkRoleMaster(Request $request, User $user): void
    {
        if ($user->role === 'mahasiswa') {
            $profile = MahasiswaProfile::firstOrNew(['nim' => $request->nim]);
            if ($profile->exists && $profile->user_id && (int) $profile->user_id !== (int) $user->id) {
                throw new \RuntimeException('NIM sudah terhubung dengan akun lain.');
            }
            $profile->fill([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'nama_lengkap' => $request->nama_lengkap ?: $user->name,
                'email' => $user->email,
                'status_mahasiswa' => $profile->status_mahasiswa ?: 'aktif',
            ]);
            $profile->syncProfileStatus();
            $profile->save();
            return;
        }

        if ($user->role === 'dosen') {
            $dosen = Dosen::firstOrNew(['nidn' => $request->nidn]);
            if ($dosen->exists && $dosen->user_id && (int) $dosen->user_id !== (int) $user->id) {
                throw new \RuntimeException('NIDN/NIP sudah terhubung dengan akun lain.');
            }
            $dosen->fill([
                'user_id' => $user->id,
                'nidn' => $request->nidn,
                'nama_dosen' => $request->nama_dosen ?: $user->name,
                'email_dosen' => $user->email,
                'status_dosen' => $dosen->status_dosen ?: 'aktif',
            ]);
            $dosen->syncProfileStatus();
            $dosen->save();
            return;
        }

        if ($user->role === 'pembimbing_lapangan') {
            $mitra = Mitra::find($request->mitra_id);
            $pembimbing = PembimbingLapangan::firstOrNew(['email' => $user->email]);
            $pembimbing->fill([
                'user_id' => $user->id,
                'mitra_id' => $request->mitra_id,
                'nama' => $request->nama_pembimbing ?: $user->name,
                'email' => $user->email,
                'no_hp' => $request->no_hp_pembimbing,
                'jabatan' => $request->jabatan_pembimbing,
                'instansi' => $mitra?->nama_instansi,
                'status' => $user->status,
            ]);
            $pembimbing->syncProfileStatus();
            $pembimbing->save();
            return;
        }

        if ($user->role === 'mitra' && $request->filled('mitra_id')) {
            MitraUser::updateOrCreate(
                ['user_id' => $user->id],
                ['mitra_id' => $request->mitra_id, 'jabatan' => $request->jabatan_mitra]
            );
        }
    }

    private function hasHistoricalData(User $user): bool
    {
        $user->loadMissing(['mahasiswaProfile', 'dosen', 'mitraUser.mitra', 'pembimbingLapangan']);
        if ($user->mahasiswaProfile && $user->mahasiswaProfile->pengajuans()->exists()) return true;
        if ($user->dosen && $user->dosen->bimbingans()->exists()) return true;
        if ($user->mitraUser?->mitra && $user->mitraUser->mitra->pengajuans()->exists()) return true;
        if ($user->pembimbingLapangan?->pengajuan_id) return true;
        return false;
    }
}