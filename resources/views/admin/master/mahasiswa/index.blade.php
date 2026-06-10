@extends('layouts.app')
@section('title', 'Data Mahasiswa')
@section('page-title', 'Data Mahasiswa')

@section('content')
@include('partials.alerts')

<div class="card p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h5 class="mb-1">Master Data Mahasiswa</h5>
            <p class="text-muted mb-0">Kelola data induk mahasiswa untuk profile, pengajuan, dan monitoring.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.master.mahasiswa.create') }}" class="btn btn-primary">Tambah Manual</a>
            <a href="{{ route('admin.master.mahasiswa.template') }}" class="btn btn-outline-secondary">Template CSV</a>
            <a href="{{ route('admin.master.mahasiswa.export') }}" class="btn btn-outline-success">Export CSV</a>
        </div>
    </div>

    <form action="{{ route('admin.master.mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="row g-2 mt-3">
        @csrf
        <div class="col-md-8">
            <label class="form-label">Import CSV/Excel</label>
            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Import Data</button>
        </div>
    </form>
</div>

<div class="card p-4 master-table-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Daftar Mahasiswa</h5>
        <span class="text-muted small">Total {{ $mahasiswas->total() }} data</span>
    </div>

    @include('admin.master.partials.table-toolbar', [
        'action' => route('admin.master.mahasiswa.index'),
        'resetUrl' => route('admin.master.mahasiswa.index'),
        'placeholder' => 'Cari NIM, nama, email, prodi, kelas, angkatan...',
        'searchId' => 'search-mahasiswa',
    ])

    <div class="master-table-wrap">
        <table class="table table-hover align-middle master-table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Program Studi</th>
                    <th>Kelas</th>
                    <th>Angkatan</th>
                    <th>Status Profile</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswas as $m)
                    <tr>
                        <td class="fw-semibold">{{ $m->nim }}</td>
                        <td class="text-wrap-cell">{{ $m->nama_lengkap }}</td>
                        <td>{{ $m->email ?: $m->user?->email ?: '-' }}</td>
                        <td>{{ $m->prodi?->nama_prodi ?: '-' }}</td>
                        <td>{{ $m->kelasMaster?->nama_kelas ?: $m->kelas ?: '-' }}</td>
                        <td>{{ $m->angkatanMaster?->tahun ?: $m->angkatan ?: '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $m->profile_status === 'lengkap' ? 'success' : 'warning' }}">
                                {{ str_replace('_',' ', $m->profile_status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.master.mahasiswa.show', $m) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.master.partials.pagination', ['paginator' => $mahasiswas])
</div>
@endsection