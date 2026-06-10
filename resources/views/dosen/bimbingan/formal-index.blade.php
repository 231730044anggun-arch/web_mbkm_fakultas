@extends('layouts.app')
@section('title', 'Riwayat Bimbingan')
@section('page-title', 'Riwayat Bimbingan')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">{{ $bimbingan->pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Tanggal</th><th>Topik</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($riwayat as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $b->tanggal_bimbingan }}</td>
                <td>{{ $b->topik }}</td>
                <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $b->status) }}</span></td>
                <td><a href="{{ route('dosen.bimbingan.formal.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada bimbingan formal</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $riwayat->links() }}
</div>
@endsection
