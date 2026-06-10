@extends('layouts.app')
@section('title', 'Data Pembimbing Lapangan')
@section('page-title', 'Data Pembimbing Lapangan')

@section('content')
@include('partials.alerts')

<div class="card p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h5 class="mb-1">Master Data Pembimbing Lapangan</h5>
            <p class="text-muted mb-0">Kelola PIC/pembimbing lapangan yang terhubung dengan mitra atau instansi.</p>
        </div>
        <a href="{{ route('admin.master.pembimbing.create') }}" class="btn btn-primary">Tambah Manual</a>
    </div>
</div>

<div class="card p-4 master-table-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Daftar Pembimbing Lapangan</h5>
        <span class="text-muted small">Total {{ $pembimbings->total() }} data</span>
    </div>

    @include('admin.master.partials.table-toolbar', [
        'action' => route('admin.master.pembimbing.index'),
        'resetUrl' => route('admin.master.pembimbing.index'),
        'placeholder' => 'Cari nama, email, no HP, mitra...',
        'searchId' => 'search-pembimbing',
    ])

    <div class="master-table-wrap">
        <table class="table table-hover align-middle master-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Jabatan</th>
                    <th>Mitra/Instansi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembimbings as $p)
                    <tr>
                        <td class="fw-semibold text-wrap-cell">{{ $p->nama }}</td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->no_hp ?: '-' }}</td>
                        <td>{{ $p->jabatan ?: '-' }}</td>
                        <td class="text-wrap-cell">{{ $p->mitra?->nama_instansi ?: $p->instansi ?: '-' }}</td>
                        <td><span class="badge bg-{{ $p->status === 'aktif' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                        <td class="text-end"><a href="{{ route('admin.master.pembimbing.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pembimbing lapangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.master.partials.pagination', ['paginator' => $pembimbings])
</div>
@endsection