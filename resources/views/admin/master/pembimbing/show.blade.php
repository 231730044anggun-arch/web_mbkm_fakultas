@extends('layouts.app')
@section('title', 'Detail Pembimbing Lapangan')
@section('page-title', 'Detail Pembimbing Lapangan')
@section('content')
<div class="card p-4">
<div class="d-flex justify-content-between mb-3"><h6 class="fw-bold">Detail Pembimbing Lapangan</h6><a href="{{ route('admin.master.pembimbing.edit', $pembimbing) }}" class="btn btn-sm btn-outline-warning">Edit</a></div>
@php($items=['Nama Pembimbing'=>$pembimbing->nama,'Email'=>$pembimbing->email,'Akun Login'=>$pembimbing->user?->email,'No HP'=>$pembimbing->no_hp,'Jabatan'=>$pembimbing->jabatan,'Mitra/Instansi'=>$pembimbing->mitra?->nama_instansi ?: $pembimbing->instansi,'Status'=>$pembimbing->status,'Status Profile'=>str_replace('_',' ', $pembimbing->profile_status),'Catatan'=>$pembimbing->catatan])
<div class="row">@foreach($items as $label=>$value)<div class="col-md-6 mb-3"><div class="text-muted small">{{ $label }}</div><div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div></div>@endforeach</div>
<a href="{{ route('admin.master.pembimbing.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection