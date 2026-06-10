@extends('layouts.app')
@section('title', 'Profile')
@section('page-title', 'Profile')

@php
    $items = [
        'Nama Akun' => $user->name,
        'Email Akun' => $user->email,
        'Role' => ucwords(str_replace('_', ' ', $user->role)),
        'Status Akun' => $user->status,
    ];

    if ($user->role === 'mahasiswa' && $user->mahasiswaProfile) {
        $m = $user->mahasiswaProfile;
        $items = array_merge($items, [
            'NIM' => $m->nim,
            'Nama Lengkap' => $m->nama_lengkap,
            'Kelas' => $m->kelasMaster?->nama_kelas ?: $m->kelas,
            'Jenis Kelamin' => $m->jenis_kelamin,
            'Alamat' => $m->alamat_lengkap,
            'Nomor HP' => $m->no_hp,
            'Tempat Lahir' => $m->tempat_lahir,
            'Tanggal Lahir' => $m->tanggal_lahir,
            'Fakultas' => $m->fakultas?->nama_fakultas ?: 'Fakultas Sains dan Teknologi',
            'Program Studi' => $m->prodi?->nama_prodi,
            'Angkatan' => $m->angkatanMaster?->tahun ?: $m->angkatan,
            'Semester' => $m->semester,
            'SKS Lulus' => $m->sks_lulus,
            'Pernah Cuti' => $m->pernah_cuti ? 'Ya' : 'Tidak',
            'IPK' => $m->ipk,
            'Status Mahasiswa' => $m->status_mahasiswa,
            'Status Profile' => str_replace('_', ' ', $m->profile_status),
        ]);
    }

    if ($user->role === 'dosen' && $user->dosen) {
        $d = $user->dosen;
        $items = array_merge($items, [
            'Nama Dosen' => $d->nama_dosen,
            'NIDN/NIP' => $d->nidn,
            'Program Studi' => $d->prodi?->nama_prodi,
            'Nomor HP' => $d->no_hp,
            'Email Dosen' => $d->email_dosen,
            'Status Dosen' => $d->status_dosen,
            'Status Profile' => str_replace('_', ' ', $d->profile_status),
        ]);
    }

    if ($user->role === 'pembimbing_lapangan' && $user->pembimbingLapangan) {
        $p = $user->pembimbingLapangan;
        $items = array_merge($items, [
            'Nama Pembimbing' => $p->nama,
            'Email Pembimbing' => $p->email,
            'Nomor HP' => $p->no_hp,
            'Jabatan' => $p->jabatan,
            'Mitra/Instansi' => $p->mitra?->nama_instansi ?: $p->instansi,
            'Status Pembimbing' => $p->status,
            'Status Profile' => str_replace('_', ' ', $p->profile_status),
        ]);
    }

    if ($user->role === 'mitra' && $user->mitraUser?->mitra) {
        $mitra = $user->mitraUser->mitra;
        $items = array_merge($items, [
            'Nama Instansi' => $mitra->nama_instansi,
            'Jenis Instansi' => $mitra->jenis_instansi,
            'Bidang Instansi' => $mitra->bidang_industri,
            'Alamat' => $mitra->alamat,
            'Kota' => $mitra->kota,
            'Email Instansi' => $mitra->email,
            'Nomor Telepon' => $mitra->no_telp,
            'Status Mitra' => $mitra->status_mitra_detail ?? $mitra->status_mitra,
        ]);
    }
@endphp

@section('content')
@include('partials.alerts')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Profile Saya</h6>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Edit Profile</a>
    </div>
    @if($user->role === 'mahasiswa' && (!$user->mahasiswaProfile || !$user->mahasiswaProfile->profileComplete()))
        <div class="alert alert-warning">Profile Anda belum lengkap. Silakan lengkapi profile terlebih dahulu sebelum mengajukan magang.</div>
    @endif
    <div class="row">
        @foreach($items as $label => $value)
        <div class="col-md-6 mb-3">
            <div class="text-muted small">{{ $label }}</div>
            <div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection