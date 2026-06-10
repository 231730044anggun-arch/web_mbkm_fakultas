<?php
namespace App\Http\Controllers;

use App\Models\Angkatan;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\Mitra;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load([
            'mahasiswaProfile.fakultas',
            'mahasiswaProfile.prodi',
            'mahasiswaProfile.kelasMaster',
            'mahasiswaProfile.angkatanMaster',
            'dosen.prodi',
            'mitraUser.mitra',
            'pembimbingLapangan.mitra',
        ]);

        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user()->load([
            'mahasiswaProfile.fakultas',
            'mahasiswaProfile.prodi',
            'mahasiswaProfile.kelasMaster',
            'mahasiswaProfile.angkatanMaster',
            'dosen.prodi',
            'mitraUser.mitra',
            'pembimbingLapangan.mitra',
        ]);
        $fakultas = Fakultas::orderBy('nama_fakultas')->get();
        $prodis = Prodi::with('fakultas')->orderBy('nama_prodi')->get();
        $kelasOptions = Kelas::where('status', 'aktif')->orderBy('nama_kelas')->get();
        $angkatanOptions = Angkatan::where('status', 'aktif')->orderByDesc('tahun')->get();
        $mitras = Mitra::orderBy('nama_instansi')->get();

        return view('profile.edit', compact('user', 'fakultas', 'prodis', 'kelasOptions', 'angkatanOptions', 'mitras'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'no_hp' => 'nullable|string|max:50',
            'email_pribadi' => 'nullable|email|max:150',
            'alamat_lengkap' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'nullable|date',
            'kelas_id' => 'nullable|exists:kelas,id',
            'angkatan_id' => 'nullable|exists:angkatans,id',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'prodi_id' => 'nullable|exists:prodis,id',
            'semester' => 'nullable|integer|min:1',
            'sks_lulus' => 'nullable|integer|min:0',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'status_mahasiswa' => 'nullable|in:aktif,cuti,lulus',
            'pernah_cuti' => 'nullable|boolean',
            'nidn' => 'nullable|string|max:80',
            'nama_dosen' => 'nullable|string|max:255',
            'status_dosen' => 'nullable|in:aktif,nonaktif',
            'nama_pembimbing' => 'nullable|string|max:150',
            'jabatan' => 'nullable|string|max:150',
            'mitra_id' => 'nullable|exists:mitras,id',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        if ($user->role === 'mahasiswa' && $user->mahasiswaProfile) {
            $kelas = isset($data['kelas_id']) ? Kelas::find($data['kelas_id']) : null;
            $angkatan = isset($data['angkatan_id']) ? Angkatan::find($data['angkatan_id']) : null;
            $profile = $user->mahasiswaProfile;
            $profile->fill([
                'nama_lengkap' => $data['name'],
                'no_hp' => $data['no_hp'] ?? $profile->no_hp,
                'email' => $data['email_pribadi'] ?? $profile->email,
                'alamat_lengkap' => $data['alamat_lengkap'] ?? $profile->alamat_lengkap,
                'tempat_lahir' => $data['tempat_lahir'] ?? $profile->tempat_lahir,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? $profile->jenis_kelamin,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? $profile->tanggal_lahir,
                'kelas_id' => $data['kelas_id'] ?? $profile->kelas_id,
                'kelas' => $kelas?->nama_kelas ?: $profile->kelas,
                'angkatan_id' => $data['angkatan_id'] ?? $profile->angkatan_id,
                'angkatan' => $angkatan?->tahun ?: $profile->angkatan,
                'fakultas_id' => $data['fakultas_id'] ?? $profile->fakultas_id,
                'prodi_id' => $data['prodi_id'] ?? $profile->prodi_id,
                'semester' => $data['semester'] ?? $profile->semester,
                'sks_lulus' => $data['sks_lulus'] ?? $profile->sks_lulus,
                'ipk' => $data['ipk'] ?? $profile->ipk,
                'status_mahasiswa' => $data['status_mahasiswa'] ?? $profile->status_mahasiswa,
                'pernah_cuti' => $request->boolean('pernah_cuti'),
            ]);
            $profile->syncProfileStatus();
            $profile->save();
        }

        if ($user->role === 'dosen' && $user->dosen) {
            $dosen = $user->dosen;
            $dosen->fill([
                'nidn' => $data['nidn'] ?? $dosen->nidn,
                'nama_dosen' => $data['nama_dosen'] ?? $data['name'],
                'prodi_id' => $data['prodi_id'] ?? $dosen->prodi_id,
                'no_hp' => $data['no_hp'] ?? $dosen->no_hp,
                'email_dosen' => $data['email_pribadi'] ?? $dosen->email_dosen,
                'status_dosen' => $data['status_dosen'] ?? $dosen->status_dosen,
            ]);
            $dosen->syncProfileStatus();
            $dosen->save();
        }

        if ($user->role === 'pembimbing_lapangan' && $user->pembimbingLapangan) {
            $mitra = isset($data['mitra_id']) ? Mitra::find($data['mitra_id']) : null;
            $pembimbing = $user->pembimbingLapangan;
            $pembimbing->fill([
                'nama' => $data['nama_pembimbing'] ?? $data['name'],
                'email' => $data['email_pribadi'] ?? $user->email,
                'no_hp' => $data['no_hp'] ?? $pembimbing->no_hp,
                'jabatan' => $data['jabatan'] ?? $pembimbing->jabatan,
                'mitra_id' => $data['mitra_id'] ?? $pembimbing->mitra_id,
                'instansi' => $mitra?->nama_instansi ?: $pembimbing->instansi,
            ]);
            $pembimbing->syncProfileStatus();
            $pembimbing->save();
        }

        if ($user->role === 'mitra' && $user->mitraUser?->mitra) {
            $user->mitraUser->mitra->update([
                'email' => $data['email_pribadi'] ?? $user->mitraUser->mitra->email,
                'no_telp' => $data['no_hp'] ?? $user->mitraUser->mitra->no_telp,
            ]);
        }

        return redirect()->route('profile.show')->with('success', 'Profile berhasil diperbarui.');
    }
}