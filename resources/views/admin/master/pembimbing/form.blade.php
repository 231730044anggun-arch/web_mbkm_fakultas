@extends('layouts.app')
@section('title', isset($pembimbing) ? 'Edit Pembimbing Lapangan' : 'Tambah Pembimbing Lapangan')
@section('page-title', isset($pembimbing) ? 'Edit Pembimbing Lapangan' : 'Tambah Pembimbing Lapangan')
@section('content')
@include('partials.alerts')
@php($p = $pembimbing ?? null)
<div class="card p-4">
<form action="{{ isset($p) ? route('admin.master.pembimbing.update', $p) : route('admin.master.pembimbing.store') }}" method="POST">
@csrf @isset($p) @method('PUT') @endisset
<div class="row g-3">
<div class="col-md-6"><label class="form-label fw-semibold">Nama Pembimbing *</label><input name="nama" class="form-control" value="{{ old('nama', $p->nama ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label fw-semibold">Email *</label><input type="email" name="email" class="form-control" value="{{ old('email', $p->email ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label fw-semibold">No HP</label><input name="no_hp" class="form-control" value="{{ old('no_hp', $p->no_hp ?? '') }}"></div>
<div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input name="jabatan" class="form-control" value="{{ old('jabatan', $p->jabatan ?? '') }}"></div>
<div class="col-md-6"><label class="form-label fw-semibold">Mitra/Instansi *</label><select name="mitra_id" class="form-select" required><option value="">-</option>@foreach($mitras as $m)<option value="{{ $m->id }}" @selected(old('mitra_id', $p->mitra_id ?? '') == $m->id)>{{ $m->nama_instansi }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="aktif" @selected(old('status', $p->status ?? 'aktif') === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $p->status ?? '') === 'nonaktif')>Nonaktif</option></select></div>
<div class="col-md-6"><div class="form-check mt-4"><input type="hidden" name="buat_akun" value="0"><input class="form-check-input" type="checkbox" name="buat_akun" value="1" id="buat_akun" @checked(old('buat_akun', $p?->user_id ? 1 : 0))><label class="form-check-label" for="buat_akun">Buat/hubungkan akun login pembimbing</label></div></div>
<div class="col-md-6"><label class="form-label fw-semibold">Password Akun</label><input type="password" name="password" class="form-control" placeholder="Kosongkan untuk default/tidak diubah"></div>
<div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $p->catatan ?? '') }}</textarea></div>
<div class="col-12"><button class="btn btn-primary">Simpan</button><a href="{{ route('admin.master.pembimbing.index') }}" class="btn btn-secondary">Kembali</a></div>
</div></form></div>
@endsection