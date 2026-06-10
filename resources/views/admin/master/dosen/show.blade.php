@extends('layouts.app')
@section('title', 'Detail Dosen')
@section('page-title', 'Detail Dosen')
@section('content')
<div class="card p-4">
<div class="d-flex justify-content-between mb-3"><h6 class="fw-bold">Detail Dosen</h6><a href="{{ route('admin.master.dosen.edit', $dosen) }}" class="btn btn-sm btn-outline-warning">Edit</a></div>
@php($items=['NIDN/NIP'=>$dosen->nidn,'Nama Dosen'=>$dosen->nama_dosen,'Email Dosen'=>$dosen->email_dosen ?: $dosen->user?->email,'Akun Login'=>$dosen->user?->email,'Program Studi'=>$dosen->prodi?->nama_prodi,'Nomor HP'=>$dosen->no_hp,'Status Dosen'=>$dosen->status_dosen,'Status Profile'=>str_replace('_',' ', $dosen->profile_status)])
<div class="row">@foreach($items as $label=>$value)<div class="col-md-6 mb-3"><div class="text-muted small">{{ $label }}</div><div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div></div>@endforeach</div>
<a href="{{ route('admin.master.dosen.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection