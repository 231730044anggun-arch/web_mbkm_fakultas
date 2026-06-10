@extends('layouts.app')
@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
@php
    $roleLabel = str_replace('_', ' ', $user->role);
    $items = [
        'Nama Akun' => $user->name,
        'Email Akun' => $user->email,
        'Role' => ucwords($roleLabel),
        'Status Akun' => $user->status,
    ];
    $masterUrl = null;
    if ($user->role === 'mahasiswa' && $user->mahasiswaProfile) {
        $m = $user->mahasiswaProfile;
        $masterUrl = route('admin.master.mahasiswa.show', $m);
        $items += [
            'NIM' => $m->nim,
            'Nama Lengkap' => $m->nama_lengkap,
            'Profile Master' => $m->profile_status,
        ];
    }
    if ($user->role === 'dosen' && $user->dosen) {
        $d = $user->dosen;
        $masterUrl = route('admin.master.dosen.show', $d);
        $items += [
            'NIDN/NIP' => $d->nidn,
            'Nama Dosen' => $d->nama_dosen,
            'Profile Master' => $d->profile_status,
        ];
    }
    if ($user->role === 'pembimbing_lapangan' && $user->pembimbingLapangan) {
        $p = $user->pembimbingLapangan;
        $masterUrl = route('admin.master.pembimbing.show', $p);
        $items += [
            'Nama Pembimbing' => $p->nama,
            'Mitra/Instansi' => $p->mitra?->nama_instansi,
            'No HP' => $p->no_hp,
            'Jabatan' => $p->jabatan,
            'Profile Master' => $p->profile_status,
        ];
    }
    if ($user->role === 'mitra' && $user->mitraUser?->mitra) {
        $items += [
            'Mitra Legacy' => $user->mitraUser->mitra->nama_instansi,
            'Jabatan User Mitra' => $user->mitraUser->jabatan,
        ];
    }
@endphp
@include('partials.alerts')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold mb-0">Detail Akun User</h6>
        <div class="d-flex gap-2">
            @if($masterUrl)
                <a href="{{ $masterUrl }}" class="btn btn-sm btn-outline-primary">Lihat Master Data</a>
            @endif
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning">Edit Akun</a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan user ini? Data historis tidak akan dihapus.')">Nonaktifkan</button>
            </form>
        </div>
    </div>
    <div class="row">
        @foreach($items as $label => $value)
            <div class="col-md-6 mb-3">
                <div class="text-muted small">{{ $label }}</div>
                <div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div>
            </div>
        @endforeach
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">Kembali</a>
</div>
@endsection