@extends('layouts.app')
@section('title', 'Bimbingan')
@section('page-title', 'Bimbingan')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar Bimbingan</h6>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Mahasiswa</th><th>NIM</th><th>Program Studi</th><th>Instansi</th><th>Status Magang</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($bimbingans as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $b->pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $b->pengajuan->mahasiswa->nim ?? '-' }}</td>
                <td>{{ $b->pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td>{{ $b->pengajuan->mitra->nama_instansi ?? $b->pengajuan->nama_instansi_manual ?? '-' }}</td>
                <td><span class="badge bg-{{ $b->pengajuan->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ $b->pengajuan->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</span></td>
                <td>
                    <a href="{{ route('dosen.bimbingan.show', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    <a href="{{ route('dosen.bimbingan.formal.index', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-secondary">Bimbingan</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada bimbingan</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

