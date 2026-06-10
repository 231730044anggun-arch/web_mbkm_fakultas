@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-4" style="border-left-color:#4361ee;">
            <div class="text-muted small">Total Mahasiswa</div>
            <div class="fs-2 fw-bold text-primary">{{ $total_mahasiswa }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4" style="border-left-color:#28a745;">
            <div class="text-muted small">Total Mitra</div>
            <div class="fs-2 fw-bold text-success">{{ $total_mitra }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4" style="border-left-color:#ffc107;">
            <div class="text-muted small">Pengajuan Pending</div>
            <div class="fs-2 fw-bold text-warning">{{ $pengajuan_pending }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4" style="border-left-color:#17a2b8;">
            <div class="text-muted small">Sedang Berjalan</div>
            <div class="fs-2 fw-bold text-info">{{ $pengajuan_berjalan }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Ringkasan Pengajuan</h6>
            <table class="table table-borderless">
                <tr><td>Total Pengajuan</td><td class="fw-bold">{{ $total_pengajuan }}</td></tr>
                <tr><td>Sedang Berjalan</td><td class="fw-bold text-info">{{ $pengajuan_berjalan }}</td></tr>
                <tr><td>Selesai</td><td class="fw-bold text-success">{{ $pengajuan_selesai }}</td></tr>
                <tr><td>Total Dosen</td><td class="fw-bold">{{ $total_dosen }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Aksi Cepat</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-text me-2"></i>Lihat Pengajuan
                </a>
                <a href="{{ route('admin.mitra.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-building me-2"></i>Data Mitra
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-info">
                    <i class="bi bi-bar-chart me-2"></i>Laporan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection