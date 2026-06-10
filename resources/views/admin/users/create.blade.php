@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
@php($selectedRole = old('role', request('role')))
@include('partials.alerts')
<div class="card p-4">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <h6 class="fw-bold mb-0">Form Dasar Akun Login</h6>
                <div class="text-muted small">Manajemen User hanya membuat akun dasar. Detail lengkap dikelola di menu Master Data.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach(['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'pembimbing_lapangan' => 'Pembimbing Lapangan', 'admin' => 'Admin', 'superadmin' => 'Superadmin'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 role-section" data-role-section="mahasiswa">
                <label class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
                <input type="text" name="nim" class="form-control" value="{{ old('nim') }}">
                @error('nim')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 role-section" data-role-section="mahasiswa">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}">
                @error('nama_lengkap')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 role-section" data-role-section="dosen">
                <label class="form-label fw-semibold">NIDN/NIP <span class="text-danger">*</span></label>
                <input type="text" name="nidn" class="form-control" value="{{ old('nidn') }}">
                @error('nidn')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 role-section" data-role-section="dosen">
                <label class="form-label fw-semibold">Nama Dosen <span class="text-danger">*</span></label>
                <input type="text" name="nama_dosen" class="form-control" value="{{ old('nama_dosen') }}">
                @error('nama_dosen')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Nama Pembimbing Lapangan <span class="text-danger">*</span></label>
                <input type="text" name="nama_pembimbing" class="form-control" value="{{ old('nama_pembimbing') }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Mitra/Instansi <span class="text-danger">*</span></label>
                <select name="mitra_id" class="form-select">
                    <option value="">-- Pilih Mitra/Instansi --</option>
                    @foreach($mitras as $mitra)
                        <option value="{{ $mitra->id }}" @selected(old('mitra_id') == $mitra->id)>{{ $mitra->nama_instansi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">No HP</label>
                <input type="text" name="no_hp_pembimbing" class="form-control" value="{{ old('no_hp_pembimbing') }}">
            </div>
            <div class="col-md-6 role-section" data-role-section="pembimbing_lapangan">
                <label class="form-label fw-semibold">Jabatan</label>
                <input type="text" name="jabatan_pembimbing" class="form-control" value="{{ old('jabatan_pembimbing') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
                @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">Kembali</a>
            </div>
        </div>
    </form>
</div>
@include('admin.users._role-script')
@endsection