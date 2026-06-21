@extends('layouts.app')
@section('title', 'Dashboard Mahasiswa')
@section('page-title', 'Dashboard')

@section('content')
@if(($mahasiswa?->isAngkatanKhususSkKolektif() ?? false))
@php
    $dosenPembimbing = $informasiMagang?->bimbingans?->first()?->dosen;
    $penempatanLengkap = $informasiMagang?->penempatanLengkap() ?? false;
    $statusMagang = $informasiMagang?->status_seminar === 'selesai' ? 'Selesai' : 'Berjalan';
    $statusMagangClass = $statusMagang === 'Selesai' ? 'success' : 'primary';
@endphp
<div class="card p-4 mb-4" style="border-left: 4px solid #7c3aed; background: linear-gradient(135deg, #faf8ff, #f3f0ff);">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h6 class="student-card-title mb-1">Informasi Magang</h6>
            <div class="text-muted small">Data penempatan magang angkatan 2023 dari SK Magang dan import penempatan admin.</div>
        </div>
        <span class="badge bg-{{ $penempatanLengkap ? 'success' : 'warning' }} student-badge">{{ $penempatanLengkap ? 'Lengkap' : 'Belum Lengkap' }}</span>
    </div>
    @if(!$penempatanLengkap)
        <div class="alert alert-warning">Data penempatan magang Anda belum lengkap. Silakan hubungi admin/fakultas.</div>
    @endif
    <div class="row g-3">
        <div class="col-md-4"><div class="small text-muted">Status Magang</div><div><span class="badge bg-{{ $statusMagangClass }} student-badge">{{ $statusMagang }}</span></div></div>
        <div class="col-md-4"><div class="small text-muted">Tempat Magang / Instansi</div><div class="fw-semibold">{{ $informasiMagang?->mitra?->nama_instansi ?? $informasiMagang?->nama_instansi_manual ?? '-' }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Tanggal Magang</div><div class="fw-semibold">{{ $informasiMagang?->tanggal_mulai ?? '-' }} s/d {{ $informasiMagang?->tanggal_selesai ?? '-' }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Dosen Pembimbing</div><div class="fw-semibold">{{ $dosenPembimbing?->nama_dosen ?? '-' }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Pembimbing Lapangan</div><div class="fw-semibold">{{ $informasiMagang?->pembimbingLapangan?->nama ?? '-' }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Email Pembimbing Lapangan</div><div class="fw-semibold">{{ $informasiMagang?->pembimbingLapangan?->email ?? '-' }}</div></div>
    </div>
</div>
@endif

@if($aktif)
<div class="card p-4 mb-4" style="border-left: 4px solid #7c3aed; background: linear-gradient(135deg, #faf8ff, #f3f0ff);">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div style="width:42px;height:42px;background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-briefcase-fill text-white"></i>
        </div>
        <div>
            <div class="fw-bold" style="color:#2d1b69;">Magang Sedang Berjalan</div>
            <div class="text-muted small">{{ $aktif->tanggal_mulai }} s/d {{ $aktif->tanggal_selesai }}</div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="small text-muted">Instansi</div>
            <div class="fw-semibold">{{ $aktif->mitra->nama_instansi ?? $aktif->nama_instansi_manual ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="small text-muted">Posisi</div>
            <div class="fw-semibold">{{ $aktif->posisi_magang }}</div>
        </div>
    </div>
    <div class="mt-3 student-action-buttons">
        <a href="{{ route('mahasiswa.logbook.index', $aktif->id) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-journal me-1"></i>Input Logbook
        </a>
        <a href="{{ route('mahasiswa.dokumen.index', $aktif->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark me-1"></i>Upload Dokumen
        </a>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card purple">
            <div class="stat-label">Total Pengajuan</div>
            <div class="stat-value">{{ $pengajuans->count() }}</div>
            <i class="bi bi-file-text stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card pink">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $pengajuans->where('status_pengajuan','pending')->count() }}</div>
            <i class="bi bi-hourglass stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card teal">
            <div class="stat-label">Selesai</div>
            <div class="stat-value">{{ $pengajuans->where('status_pengajuan','selesai')->count() }}</div>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="student-card-title">Riwayat Pengajuan</div>
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Ajukan Magang
        </a>
    </div>
    <div class="table-responsive">
    <table class="table student-table">
        <thead>
            <tr>
                <th>Instansi</th>
                <th>Posisi</th>
                <th>Periode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $p)
            <tr>
                <td>{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual ?? '-' }}</td>
                <td>{{ $p->posisi_magang }}</td>
                <td>{{ $p->periode->nama_periode ?? '-' }}</td>
                <td>
                    @php
                        $badges = [
                            'pending'   => 'badge-yellow',
                            'disetujui' => 'badge-green',
                            'ditolak'   => 'badge-red',
                            'berjalan'  => 'badge-blue',
                            'selesai'   => 'badge-green',
                        ];
                        $statusLabel = ucwords(str_replace('_', ' ', $p->status_pengajuan));
                    @endphp
                    <span class="badge {{ $badges[$p->status_pengajuan] ?? 'badge-gray' }} student-badge">
                        {{ $statusLabel }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengajuan</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
