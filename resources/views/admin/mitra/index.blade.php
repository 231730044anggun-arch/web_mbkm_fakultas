@extends('layouts.app')
@section('title', 'Data Mitra')
@section('page-title', 'Data Mitra')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Daftar Mitra</h6>
        <a href="{{ route('admin.mitra.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Tambah Mitra
        </a>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama, kota, bidang atau email">
        </div>
        <div class="col-md-2">
            <select name="jenis_mitra" class="form-select">
                <option value="">Semua Jenis</option>
                <option value="ber_mou" {{ request('jenis_mitra') === 'ber_mou' ? 'selected' : '' }}>Ber-MoU</option>
                <option value="non_mou" {{ request('jenis_mitra') === 'non_mou' ? 'selected' : '' }}>Non-MoU</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status_mitra_detail" class="form-select">
                <option value="">Semua Status</option>
                <option value="menunggu_verifikasi" {{ request('status_mitra_detail') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="aktif" {{ request('status_mitra_detail') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status_mitra_detail') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status_mou" class="form-select">
                <option value="">Semua MoU</option>
                <option value="aktif" {{ request('status_mou') === 'aktif' ? 'selected' : '' }}>MoU Aktif</option>
                <option value="tidak" {{ request('status_mou') === 'tidak' ? 'selected' : '' }}>Tanpa MoU</option>
                <option value="expired" {{ request('status_mou') === 'expired' ? 'selected' : '' }}>MoU Expired</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filter</button>
        </div>
    </form>
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama Instansi</th>
                <th>Jenis</th>
                <th>Kota</th>
                <th>Status Mitra</th>
                <th>Status MoU</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mitras as $i => $m)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $m->nama_instansi }}</td>
                <td>{{ $m->jenis_mitra === 'ber_mou' ? 'Ber-MoU' : 'Non-MoU' }}</td>
                <td>{{ $m->kota ?? '-' }}</td>
                <td><span class="badge bg-{{ $m->status_mitra_detail === 'aktif' ? 'success' : ($m->status_mitra_detail === 'menunggu_verifikasi' ? 'warning' : 'secondary') }}">{{ ucfirst(str_replace('_', ' ', $m->status_mitra_detail ?? 'aktif')) }}</span></td>
                <td><span class="badge bg-{{ $m->status_mou === 'aktif' ? 'success' : ($m->status_mou === 'expired' ? 'warning' : 'secondary') }}">{{ $m->status_mou ?? 'tidak' }}</span></td>
                <td>
                    <a href="{{ route('admin.mitra.show', $m->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    <a href="{{ route('admin.mitra.edit', $m->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                    <form action="{{ route('admin.mitra.destroy', $m->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada mitra</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $mitras->links() }}
</div>
@endsection
