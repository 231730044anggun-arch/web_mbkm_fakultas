@extends('layouts.app')
@section('title', 'Pedoman & SOP')
@section('page-title', 'Pedoman & SOP')

@section('content')
@php($canManagePedoman = in_array(auth()->user()->role, ['admin', 'superadmin']))
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Daftar Pedoman & SOP</h6>
        @if($canManagePedoman)
        <a href="{{ route('admin.pedoman.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Tambah
        </a>
        @endif
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Judul</th><th>Kategori</th><th>Tahun</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($pedomans as $i => $p)
            @php($hasFile = $p->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($p->file_path))
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->judul }}</td>
                <td><span class="badge bg-info">{{ $p->kategori }}</span></td>
                <td>{{ $p->tahun ?? '-' }}</td>
                <td>
                    @if($hasFile)
                    <a href="{{ route('pedoman.preview', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                    <a href="{{ route('pedoman.download', $p->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                    @else
                    <span class="text-muted small">File belum tersedia</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada pedoman</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $pedomans->links() }}
</div>
@endsection

