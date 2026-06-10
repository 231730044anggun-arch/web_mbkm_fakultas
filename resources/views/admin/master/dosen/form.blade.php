@extends('layouts.app')
@section('title', isset($dosen) ? 'Edit Dosen' : 'Tambah Dosen')
@section('page-title', isset($dosen) ? 'Edit Dosen' : 'Tambah Dosen')
@section('content')
@include('partials.alerts')
@php($d = $dosen ?? null)
<div class="card p-4">
<form action="{{ isset($d) ? route('admin.master.dosen.update', $d) : route('admin.master.dosen.store') }}" method="POST">
@csrf @isset($d) @method('PUT') @endisset
<div class="row g-3">
<div class="col-md-6"><label class="form-label fw-semibold">NIDN/NIP *</label><input name="nidn" class="form-control" value="{{ old('nidn', $d->nidn ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label fw-semibold">Nama Dosen *</label><input name="nama_dosen" class="form-control" value="{{ old('nama_dosen', $d->nama_dosen ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label fw-semibold">Email Dosen</label><input type="email" name="email_dosen" class="form-control" value="{{ old('email_dosen', $d->email_dosen ?? $d?->user?->email) }}"></div>
<div class="col-md-6"><label class="form-label fw-semibold">Program Studi</label><select name="prodi_id" class="form-select"><option value="">-</option>@foreach($prodis as $p)<option value="{{ $p->id }}" @selected(old('prodi_id', $d->prodi_id ?? '') == $p->id)>{{ $p->nama_prodi }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label fw-semibold">Nomor HP</label><input name="no_hp" class="form-control" value="{{ old('no_hp', $d->no_hp ?? '') }}"></div>
<div class="col-md-6"><label class="form-label fw-semibold">Status Dosen</label><select name="status_dosen" class="form-select"><option value="aktif" @selected(old('status_dosen', $d->status_dosen ?? 'aktif') === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status_dosen', $d->status_dosen ?? '') === 'nonaktif')>Nonaktif</option></select></div>
<div class="col-12"><button class="btn btn-primary">Simpan</button><a href="{{ route('admin.master.dosen.index') }}" class="btn btn-secondary">Kembali</a></div>
</div></form></div>
@endsection