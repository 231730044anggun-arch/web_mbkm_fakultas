@extends('layouts.app')
@section('title', 'Periode')
@section('page-title', 'Manajemen Periode')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Daftar Periode</h6>
        <a href="{{ route('admin.periode.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Tambah Periode
        </a>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Nama Periode</th><th>Tahun</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($periodes as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->nama_periode }}</td>
                <td>{{ $p->tahun }}</td>
                <td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                <td><span class="badge bg-{{ $p->status === 'aktif' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                <td>
                    <a href="{{ route('admin.periode.edit', $p->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                    @if($p->status !== 'aktif')
                    <form action="{{ route('admin.periode.activate', $p->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan periode ini?')">Aktifkan</button>
                    </form>
                    @endif
                    <form action="{{ route('admin.periode.destroy', $p->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Belum ada periode</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $periodes->links() }}
</div>
@endsection
