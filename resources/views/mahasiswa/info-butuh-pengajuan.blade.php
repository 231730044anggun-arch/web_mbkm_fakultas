@extends('layouts.app')
@section('title', $feature)
@section('page-title', $feature)

@section('content')
<div class="card p-4">
    <div class="d-flex align-items-start gap-3">
        <div class="brand-icon"><i class="bi bi-info-circle"></i></div>
        <div>
            <h6 class="fw-bold mb-2">{{ $feature }} Belum Tersedia</h6>
            <p class="text-muted mb-3">
                {{ $message ?? 'Menu ini membutuhkan pengajuan magang yang sudah disetujui atau sedang berjalan. Silakan buat pengajuan terlebih dahulu atau tunggu pengajuan Anda disetujui admin.' }}
            </p>
            <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-primary btn-sm">Lihat Pengajuan</a>
            <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-secondary btn-sm">Buat Pengajuan</a>
        </div>
    </div>
</div>
@endsection
