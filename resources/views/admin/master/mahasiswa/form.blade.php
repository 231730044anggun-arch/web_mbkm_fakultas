@extends('layouts.app')
@section('title', isset($mahasiswa) ? 'Edit Mahasiswa' : 'Tambah Mahasiswa')
@section('page-title', isset($mahasiswa) ? 'Edit Mahasiswa' : 'Tambah Mahasiswa')
@section('content')
@include('partials.alerts')
@php($m = $mahasiswa ?? null)
<div class="card p-4">
    <form action="{{ isset($m) ? route('admin.master.mahasiswa.update', $m) : route('admin.master.mahasiswa.store') }}" method="POST">
        @csrf
        @isset($m) @method('PUT') @endisset
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">NIM *</label><input name="nim" class="form-control" value="{{ old('nim', $m->nim ?? '') }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Nama Lengkap *</label><input name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $m->nama_lengkap ?? '') }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $m->email ?? $m?->user?->email) }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Kelas</label><select name="kelas_id" class="form-select"><option value="">-</option>@foreach($kelasOptions as $k)<option value="{{ $k->id }}" @selected(old('kelas_id', $m->kelas_id ?? '') == $k->id)>{{ $k->nama_kelas }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="">-</option><option value="Laki-laki" @selected(old('jenis_kelamin', $m->jenis_kelamin ?? '') === 'Laki-laki')>Laki-laki</option><option value="Perempuan" @selected(old('jenis_kelamin', $m->jenis_kelamin ?? '') === 'Perempuan')>Perempuan</option></select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Nomor HP</label><input name="no_hp" class="form-control" value="{{ old('no_hp', $m->no_hp ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Alamat</label><input name="alamat_lengkap" class="form-control" value="{{ old('alamat_lengkap', $m->alamat_lengkap ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tempat Lahir</label><input name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $m->tempat_lahir ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $m->tanggal_lahir ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Angkatan</label><select name="angkatan_id" class="form-select"><option value="">-</option>@foreach($angkatanOptions as $a)<option value="{{ $a->id }}" @selected(old('angkatan_id', $m->angkatan_id ?? '') == $a->id)>{{ $a->tahun }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Fakultas</label><select name="fakultas_id" class="form-select"><option value="">-</option>@foreach($fakultas as $f)<option value="{{ $f->id }}" @selected(old('fakultas_id', $m->fakultas_id ?? '') == $f->id)>{{ $f->nama_fakultas }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Program Studi</label><select name="prodi_id" class="form-select"><option value="">-</option>@foreach($prodis as $p)<option value="{{ $p->id }}" @selected(old('prodi_id', $m->prodi_id ?? '') == $p->id)>{{ $p->nama_prodi }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Semester</label><input type="number" name="semester" class="form-control" value="{{ old('semester', $m->semester ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">SKS Lulus</label><input type="number" name="sks_lulus" class="form-control" value="{{ old('sks_lulus', $m->sks_lulus ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">IPK</label><input type="number" step="0.01" name="ipk" class="form-control" value="{{ old('ipk', $m->ipk ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Status Mahasiswa</label><select name="status_mahasiswa" class="form-select"><option value="aktif" @selected(old('status_mahasiswa', $m->status_mahasiswa ?? 'aktif') === 'aktif')>Aktif</option><option value="cuti" @selected(old('status_mahasiswa', $m->status_mahasiswa ?? '') === 'cuti')>Cuti</option><option value="lulus" @selected(old('status_mahasiswa', $m->status_mahasiswa ?? '') === 'lulus')>Lulus</option></select></div>
            <div class="col-12"><button class="btn btn-primary">Simpan</button><a href="{{ route('admin.master.mahasiswa.index') }}" class="btn btn-secondary">Kembali</a></div>
        </div>
    </form>
</div>
@endsection