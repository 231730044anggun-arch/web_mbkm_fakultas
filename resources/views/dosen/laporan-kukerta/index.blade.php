@extends('layouts.app')
@section('title', 'Laporan Kukerta')
@section('page-title', 'Laporan Kukerta Mahasiswa')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Laporan Kukerta</h6>
        <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Mahasiswa</th><th>NIM</th><th>Program Studi</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($laporans as $laporan)
                <tr>
                    <td>{{ $laporan->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $laporan->mahasiswa->nim ?? '-' }}</td>
                    <td>{{ $laporan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $laporan->lokasi_kukerta }}</td>
                    <td><span class="badge bg-success">{{ $laporan->status }}</span></td>
                    <td><a href="{{ route('dosen.laporan-kukerta.show', $laporan->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada laporan Kukerta dari mahasiswa bimbingan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $laporans->links() }}
</div>
@endsection