@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
@php
    $selectedRole = old('role', $user->role);
    $m = $user->mahasiswaProfile;
    $d = $user->dosen;
    $p = $user->pembimbingLapangan;
@endphp
@include('partials.alerts')
<div class="card p-4">
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <h6 class="fw-bold mb-0">Form Dasar Akun Login</h6>
                <div class="text-muted small">Detail lengkap tetap dikelola di menu Master Data.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    @foreach(['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'pembimbing_lapangan' => 'Pembimbing Lapangan', 'mitra' => 'Mitra Legacy', 'admin' => 'Admin', 'superadmin' => 'Superadmin'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="aktif" @selected(old('status', $user->status) === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status', $user->status) === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Password Baru</label>
                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="col-md-6 role-section" data-role-section="mahasiswa">
                <label class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
                <input type="text" name="nim" class="form-control" value="{{ old('nim', $m->nim ?? '') }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="mahasiswa">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $m->nama_lengkap ?? $user->name) }}">
            </div>

            <div class="col-md-6 role-section" data-role-section="dosen">
                <label class="form-label fw-semibold">NIDN/NIP <span class="text-danger">*</span></label>
                <input type="text" name="nidn" class="form-control" value="{{ old('nidn', $d->nidn ?? '') }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="dosen">
                <label class="form-label fw-semibold">Nama Dosen <span class="text-danger">*</span></label>
                <input type="text" name="nama_dosen" class="form-control" value="{{ old('nama_dosen', $d->nama_dosen ?? $user->name) }}">
            </div>

            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Nama Pembimbing Lapangan <span class="text-danger">*</span></label>
                <input type="text" name="nama_pembimbing" class="form-control" value="{{ old('nama_pembimbing', $p->nama ?? $user->name) }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Mitra/Instansi <span class="text-danger">*</span></label>
                <select name="mitra_id" class="form-select">
                    <option value="">-- Pilih Mitra/Instansi --</option>
                    @foreach($mitras as $mitra)
                        <option value="{{ $mitra->id }}" @selected(old('mitra_id', $p->mitra_id ?? '') == $mitra->id)>{{ $mitra->nama_instansi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">No HP</label>
                <input type="text" name="no_hp_pembimbing" class="form-control" value="{{ old('no_hp_pembimbing', $p->no_hp ?? '') }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Jabatan</label>
                <input type="text" name="jabatan_pembimbing" class="form-control" value="{{ old('jabatan_pembimbing', $p->jabatan ?? '') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary px-4">Kembali</a>
            </div>
        </div>
    </form>
</div>
@include('admin.users._role-script')
@endsection