@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('page-title', 'Detail Bimbingan')

@section('content')
@include('partials.alerts')
@php
    $statusLabel = $bimbingan->status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $bimbingan->status ?: '-'));
@endphp
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">{{ $bimbingan->topik }}</h6>
            <div class="text-muted small">{{ $bimbingan->mahasiswa->nama_lengkap ?? '-' }} / {{ $bimbingan->mahasiswa->nim ?? '-' }}</div>
        </div>
        <a href="{{ route('pembimbing.bimbingan.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="text-muted small">Tanggal</div><div class="fw-semibold">{{ $bimbingan->tanggal_bimbingan }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Mitra/Instansi</div><div class="fw-semibold">{{ $bimbingan->pengajuan->mitra->nama_instansi ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge bg-{{ in_array($bimbingan->status, ['selesai', 'dibalas'], true) ? 'success' : 'secondary' }}">{{ $statusLabel }}</span></div>
        <div class="col-12"><div class="text-muted small">Catatan/Kendala Mahasiswa</div><div>{{ $bimbingan->catatan_mahasiswa ?: '-' }}</div></div>
        <div class="col-12"><div class="text-muted small">Lampiran</div><div>@if($bimbingan->lampiran)<a href="{{ route('pembimbing.bimbingan.formal.file', $bimbingan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>@else - @endif</div></div>
        <div class="col-12"><div class="text-muted small">Balasan Pembimbing Lapangan</div><div>{{ $bimbingan->balasan_pembimbing ?: '-' }}</div></div>
    </div>
    <form action="{{ route('pembimbing.bimbingan.formal.reply', $bimbingan->id) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-12">
            <label class="form-label fw-semibold">Balasan/Catatan Pembimbing Lapangan</label>
            <textarea name="balasan_pembimbing" class="form-control" rows="4" required>{{ old('balasan_pembimbing', $bimbingan->balasan_pembimbing) }}</textarea>
        </div>
        <div class="col-12">
            <div class="alert alert-info py-2 mb-0">Setelah balasan disimpan, status bimbingan otomatis menjadi <strong>Selesai</strong>.</div>
        </div>
        <div class="col-12"><button class="btn btn-primary">Simpan Balasan</button></div>
    </form>
</div>
@endsection
