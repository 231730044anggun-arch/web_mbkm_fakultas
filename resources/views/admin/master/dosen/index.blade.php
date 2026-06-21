@extends('layouts.app')
@section('title', 'Data Dosen')
@section('page-title', 'Data Dosen')

@section('content')
@include('partials.alerts')

<div class="card p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h5 class="mb-1">Master Data Dosen</h5>
            <p class="text-muted mb-0">Kelola data induk dosen untuk bimbingan, seminar, dan penilaian.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.master.dosen.create') }}" class="btn btn-primary">Tambah Manual</a>
            <a href="{{ route('admin.master.dosen.template') }}" class="btn btn-outline-secondary">Template CSV</a>
            <a href="{{ route('admin.master.dosen.template-xlsx') }}" class="btn btn-outline-secondary">Template XLSX</a>
            <a href="{{ route('admin.master.dosen.export') }}" class="btn btn-outline-success">Export CSV</a>
        </div>
    </div>

    <form action="{{ route('admin.master.dosen.import') }}" method="POST" enctype="multipart/form-data" class="row g-2 mt-3">
        @csrf
        <div class="col-md-8">
            <label class="form-label">Import CSV/XLSX</label>
            <input type="file" name="file" class="form-control" accept=".csv,.xlsx" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Import Data</button>
        </div>
    </form>
</div>

<div class="card p-4 master-table-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Daftar Dosen</h5>
        <span class="text-muted small">Total {{ $dosens->total() }} data</span>
    </div>

    @include('admin.master.partials.table-toolbar', [
        'action' => route('admin.master.dosen.index'),
        'resetUrl' => route('admin.master.dosen.index'),
        'placeholder' => 'Cari NIDN/NIP, nama, email, prodi...',
        'searchId' => 'search-dosen',
    ])

    <div class="master-table-wrap">
        <table class="table table-hover align-middle master-table">
            <thead>
                <tr>
                    <th>NIDN/NIP</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Program Studi</th>
                    <th>No HP</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosens as $d)
                    <tr>
                        <td class="fw-semibold">{{ $d->nidn }}</td>
                        <td class="text-wrap-cell">{{ $d->nama_dosen }}</td>
                        <td>{{ $d->email_dosen ?: $d->user?->email ?: '-' }}</td>
                        <td>{{ $d->prodi?->nama_prodi ?: '-' }}</td>
                        <td>{{ $d->no_hp ?: '-' }}</td>
                        <td><span class="badge bg-{{ $d->status_dosen === 'aktif' ? 'success' : 'secondary' }}">{{ $d->status_dosen }}</span></td>
                        <td class="text-end"><a href="{{ route('admin.master.dosen.show', $d) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data dosen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.master.partials.pagination', ['paginator' => $dosens])
</div>
@endsection
