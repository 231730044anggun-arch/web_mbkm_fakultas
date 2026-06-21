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
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Mahasiswa</th>
                    <th class="align-top">Program Studi</th>
                    <th class="align-top">Instansi</th>
                    <th class="align-top">Tanggal Magang</th>
                    <th class="align-top">Pembimbing Lapangan</th>
                    <th class="align-top">Status</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bimbingans as $b)
                <tr>
                    <td class="align-top">{{ $b->pengajuan->mahasiswa->nama_lengkap ?? '-' }}<div class="text-muted small">{{ $b->pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                    <td class="align-top">{{ $b->pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td class="align-top">{{ $b->pengajuan->mitra->nama_instansi ?? $b->pengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td class="align-top">{{ $b->pengajuan->tanggal_mulai ?? '-' }} s/d {{ $b->pengajuan->tanggal_selesai ?? '-' }}</td>
                    <td class="align-top">{{ $b->pengajuan->pembimbingLapangan->nama ?? '-' }}</td>
                    <td class="align-top"><span class="badge bg-{{ $b->status === 'aktif' ? 'success' : 'secondary' }}">{{ ucwords(str_replace('_', ' ', $b->status)) }}</span></td>
                    <td class="align-top">
                        <a href="{{ route('dosen.bimbingan.show', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada bimbingan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
