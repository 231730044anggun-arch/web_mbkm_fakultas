@extends('layouts.app')
@section('title', 'Riwayat Bimbingan')
@section('page-title', 'Riwayat Bimbingan')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">{{ $bimbingan->pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
    @php
        $formatStatus = fn($status) => $status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $status ?: '-'));
    @endphp
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th class="align-top">No</th><th class="align-top">Tanggal</th><th class="align-top">Topik</th><th class="align-top">Status</th><th class="align-top">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($riwayat as $i => $b)
            <tr style="vertical-align: top;">
                <td class="align-top">{{ $i + 1 }}</td>
                <td class="align-top">{{ $b->tanggal_bimbingan }}</td>
                <td class="align-top">{{ $b->topik }}</td>
                <td class="align-top"><span class="badge bg-{{ in_array($b->status, ['selesai', 'dibalas'], true) ? 'success' : 'secondary' }}">{{ $formatStatus($b->status) }}</span></td>
                <td class="align-top"><a href="{{ route('dosen.bimbingan.formal.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada bimbingan formal</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $riwayat->links() }}
</div>
@endsection
