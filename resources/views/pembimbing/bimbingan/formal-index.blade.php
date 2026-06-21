@extends('layouts.app')
@section('title', 'Riwayat Bimbingan')
@section('page-title', 'Riwayat Bimbingan')

@section('content')
<div class="card p-4">
    @php
        $formatStatus = fn($status) => $status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $status ?: '-'));
    @endphp
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
            <div class="text-muted small">{{ $pengajuan->mahasiswa->nim ?? '-' }} / {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
            <div class="text-muted small">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</div>
        </div>
        <a href="{{ route('pembimbing.bimbingan.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th class="align-top">No</th>
                    <th class="align-top">Tanggal</th>
                    <th class="align-top">Topik</th>
                    <th class="align-top">Catatan Mahasiswa</th>
                    <th class="align-top">Status</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $i => $b)
                    <tr style="vertical-align: top;">
                        <td class="align-top">{{ $riwayat->firstItem() + $i }}</td>
                        <td class="align-top">{{ $b->tanggal_bimbingan ?? '-' }}</td>
                        <td class="align-top">{{ $b->topik ?? '-' }}</td>
                        <td class="align-top">{{ \Illuminate\Support\Str::limit($b->catatan_mahasiswa ?? '-', 120) }}</td>
                        <td class="align-top"><span class="badge bg-{{ in_array($b->status, ['selesai', 'dibalas'], true) ? 'success' : 'secondary' }}">{{ $formatStatus($b->status) }}</span></td>
                        <td class="align-top">
                            <a href="{{ route('pembimbing.bimbingan.formal.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada bimbingan formal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $riwayat->links() }}
</div>
@endsection
