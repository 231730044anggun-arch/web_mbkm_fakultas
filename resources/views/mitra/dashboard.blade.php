@extends('layouts.app')
@section('title', 'Dashboard Mitra')
@section('page-title', 'Dashboard')

@section('content')
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Mahasiswa Magang Aktif</h6>
        <span class="badge bg-primary fs-6">{{ $pengajuans->count() }} mahasiswa</span>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>Nama</th><th>Posisi</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $p)
            <tr>
                <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $p->posisi_magang }}</td>
                <td>{{ $p->tanggal_mulai }}</td>
                <td>{{ $p->tanggal_selesai }}</td>
                <td>
                    <a href="{{ route('mitra.penilaian.create', $p->id) }}" class="btn btn-sm btn-outline-primary">Beri Nilai</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada mahasiswa magang</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection