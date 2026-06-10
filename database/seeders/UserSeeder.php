<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MahasiswaProfile;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Mitra;
use App\Models\MitraUser;
use App\Models\Periode;
use App\Models\Prodi;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $fakultas = Fakultas::updateOrCreate(
            ['nama_fakultas' => 'Fakultas Teknik'],
            ['nama_fakultas' => 'Fakultas Teknik']
        );

        $prodi = Prodi::updateOrCreate(
            ['nama_prodi' => 'Informatika'],
            ['nama_prodi' => 'Informatika']
        );

        Periode::where('status', 'aktif')->update(['status' => 'nonaktif']);
        Periode::updateOrCreate(
            ['nama_periode' => 'Periode Magang 2026'],
            [
                'tahun' => 2026,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'aktif',
            ]
        );

        $mitraBerMou = Mitra::updateOrCreate(
            ['nama_instansi' => 'PT Inovasi Digital Nusantara'],
            [
                'jenis_instansi' => 'Perusahaan',
                'bidang_industri' => 'Teknologi Informasi',
                'alamat' => 'Jl. Merdeka No. 10',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'email' => 'hr@inovasidigital.test',
                'no_telp' => '021123456',
                'website' => 'https://inovasidigital.test',
                'status_mitra' => 'terdaftar',
                'status_mitra_detail' => 'aktif',
                'jenis_mitra' => 'ber_mou',
                'nomor_mou' => 'MOU/MBKM/2026/001',
                'tanggal_mulai_mou' => '2026-01-01',
                'tanggal_berakhir_mou' => '2026-12-31',
                'status_mou' => 'aktif',
                'pembimbing_lapangan_nama' => 'Rina Pratiwi',
                'pembimbing_lapangan_jabatan' => 'HR Manager',
                'pembimbing_lapangan_kontak' => '081200000001',
            ]
        );

        Mitra::updateOrCreate(
            ['nama_instansi' => 'CV Kreatif Teknologi'],
            [
                'jenis_instansi' => 'Perusahaan',
                'bidang_industri' => 'Software House',
                'alamat' => 'Jl. Kaliurang No. 22',
                'kota' => 'Yogyakarta',
                'provinsi' => 'DI Yogyakarta',
                'email' => 'kontak@kreatifteknologi.test',
                'no_telp' => '0274123456',
                'status_mitra' => 'terdaftar',
                'status_mitra_detail' => 'aktif',
                'jenis_mitra' => 'non_mou',
                'status_mou' => 'tidak',
                'pembimbing_lapangan_nama' => 'Dimas Arya',
                'pembimbing_lapangan_jabatan' => 'Project Lead',
                'pembimbing_lapangan_kontak' => '081200000002',
            ]
        );

        User::updateOrCreate(
            ['email' => 'superadmin@mbkm.ac.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'status' => 'aktif',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@mbkm.ac.id'],
            [
                'name' => 'Admin MBKM',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        $mahasiswa = User::updateOrCreate(
            ['email' => 'mahasiswa@mbkm.ac.id'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'status' => 'aktif',
            ]
        );

        MahasiswaProfile::updateOrCreate(
            ['user_id' => $mahasiswa->id],
            [
                'nim' => '2021001',
                'nama_lengkap' => 'Ahmad Fauzi',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2003-04-15',
                'no_hp' => '08123456789',
                'email' => 'ahmad.fauzi@student.test',
                'alamat_lengkap' => 'Jl. Pendidikan No. 5',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40123',
                'fakultas_id' => $fakultas->id,
                'prodi_id' => $prodi->id,
                'angkatan' => 2023,
                'semester' => 5,
                'sks_lulus' => 100,
                'pernah_cuti' => false,
                'ipk' => 3.75,
                'status_mahasiswa' => 'aktif',
            ]
        );

        $dosen = User::updateOrCreate(
            ['email' => 'dosen@mbkm.ac.id'],
            [
                'name' => 'Dr. Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'status' => 'aktif',
            ]
        );

        Dosen::updateOrCreate(
            ['user_id' => $dosen->id],
            [
                'nidn' => '0012345678',
                'nama_dosen' => 'Dr. Budi Santoso',
                'prodi_id' => $prodi->id,
                'no_hp' => '08234567890',
            ]
        );

        $mitraUser = User::updateOrCreate(
            ['email' => 'mitra@mbkm.ac.id'],
            [
                'name' => 'PIC PT Inovasi Digital Nusantara',
                'password' => Hash::make('password'),
                'role' => 'mitra',
                'status' => 'aktif',
            ]
        );

        MitraUser::updateOrCreate(
            ['user_id' => $mitraUser->id],
            [
                'mitra_id' => $mitraBerMou->id,
                'jabatan' => 'PIC Magang',
            ]
        );
    }
}
