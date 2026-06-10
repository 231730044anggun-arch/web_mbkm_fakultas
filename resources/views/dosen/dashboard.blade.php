@extends('layouts.app')
@section('title', 'Dashboard Dosen')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card stat-card p-4" style="border-left-color:#4361ee;">
            <div class="text-muted small">Mahasiswa Bimbingan</div>
            <div class="fs-2 fw-bold text-primary">{{ $bimbingans->count() }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card p-4" style="border-left-color:#ffc107;">
            <div class="text-muted small">Logbook Pending</div>
            <div class="fs-2 fw-bold text-warning">{{ $logbookPending }}</div>
        </div>
    </div>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Mahasiswa Bimbingan</h6>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>Mahasiswa</th><th>Instansi</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($bimbingans as $b)
            <tr>
                <td>{{ $b->pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $b->pengajuan->mitra->nama_instansi ?? $b->pengajuan->nama_instansi_manual ?? '-' }}</td>
                <td><span class="badge bg-{{ $b->status === 'aktif' ? 'success' : 'secondary' }}">{{ $b->status }}</span></td>
                <td>
                    <a href="{{ route('dosen.logbook.show', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-primary">Logbook</a>
                    <a href="{{ route('dosen.penilaian.create', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-success">Nilai</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Belum ada bimbingan</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
