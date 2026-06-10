@extends('layouts.app')
@section('title', 'Bimbingan')
@section('page-title', 'Bimbingan')

@section('content')
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-1">Bimbingan Formal</h6>
            <div class="text-muted small">Dosen pembimbing: {{ $pengajuan->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}</div>
        </div>
        <a href="{{ route('mahasiswa.bimbingan.create') }}" class="btn btn-primary btn-sm">Tambah Bimbingan</a>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Tanggal</th><th>Topik</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($bimbingans as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $b->tanggal_bimbingan }}</td>
                <td>{{ $b->topik }}</td>
                <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $b->status) }}</span></td>
                <td>
    <a href="{{ route('mahasiswa.bimbingan.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
    @if($b->status === 'menunggu_balasan' && blank($b->balasan_dosen))
        <form action="{{ route('mahasiswa.bimbingan.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bimbingan ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Hapus</button>
        </form>
    @endif
</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada bimbingan</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $bimbingans->links() }}
</div>
@endsection
