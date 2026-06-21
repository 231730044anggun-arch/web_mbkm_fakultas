@extends('layouts.app')
@section('title', 'Bimbingan')
@section('page-title', 'Bimbingan')

@section('content')
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="student-card-title mb-1">Bimbingan Formal</h6>
            <div class="text-muted small">Dosen pembimbing: {{ $pengajuan->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}</div>
            <div class="text-muted small">Pembimbing lapangan: {{ $pengajuan->pembimbingLapangan?->nama ?? '-' }}</div>
        </div>
        <a href="{{ route('mahasiswa.bimbingan.create') }}" class="btn btn-primary btn-sm">Tambah Bimbingan</a>
    </div>
    <div class="table-responsive">
    <table class="table table-hover student-table">
        <thead class="table-light">
            <tr><th class="align-top">No</th><th class="align-top">Tanggal</th><th class="align-top">Tujuan</th><th class="align-top">Topik</th><th class="align-top">Status</th><th class="align-top">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($bimbingans as $i => $b)
            @php
                $statusClass = [
                    'menunggu_balasan' => 'warning',
                    'dibalas' => 'success',
                    'selesai' => 'success',
                    'revisi' => 'warning',
                    'ditolak' => 'danger',
                ][$b->status] ?? 'secondary';
                $statusLabel = $b->status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $b->status));
            @endphp
            <tr>
                <td class="align-top">{{ $i + 1 }}</td>
                <td class="align-top">{{ $b->tanggal_bimbingan }}</td>
                <td class="align-top">{{ $b->tujuan_bimbingan === 'pembimbing_lapangan' ? 'Pembimbing Lapangan' : 'Dosen Pembimbing' }}</td>
                <td class="align-top">{{ $b->topik }}</td>
                <td class="align-top"><span class="badge bg-{{ $statusClass }} student-badge">{{ $statusLabel }}</span></td>
                <td class="align-top">
                    <div class="student-action-buttons">
                        <a href="{{ route('mahasiswa.bimbingan.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @if($b->status === 'menunggu_balasan' && blank($b->balasan_dosen))
                            <form action="{{ route('mahasiswa.bimbingan.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bimbingan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Belum ada bimbingan</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($bimbingans->hasMorePages() || $bimbingans->currentPage() > 1)
    <div class="mt-3 d-flex justify-content-end gap-2">
        @if($bimbingans->onFirstPage())
            <span class="btn btn-sm btn-secondary disabled">Sebelumnya</span>
        @else
            <a href="{{ $bimbingans->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">Sebelumnya</a>
        @endif
        @if($bimbingans->hasMorePages())
            <a href="{{ $bimbingans->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">Berikutnya</a>
        @else
            <span class="btn btn-sm btn-secondary disabled">Berikutnya</span>
        @endif
    </div>
    @endif
</div>
@endsection
