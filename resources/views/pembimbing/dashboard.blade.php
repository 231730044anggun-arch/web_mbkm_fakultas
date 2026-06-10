@extends('layouts.app')
@section('title', 'Dashboard Pembimbing Lapangan')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-4"><div class="text-muted small">Mahasiswa Bimbingan</div><h3 class="mb-0">{{ $pengajuans->count() }}</h3></div></div>
    <div class="col-md-4"><div class="card p-4"><div class="text-muted small">Magang Aktif</div><h3 class="mb-0">{{ $pengajuans->where('status_pengajuan', 'berjalan')->count() }}</h3></div></div>
    <div class="col-md-4"><div class="card p-4"><div class="text-muted small">Instansi</div><h6 class="mb-0">{{ $pembimbing->mitra->nama_instansi ?? $pembimbing->instansi ?? '-' }}</h6></div></div>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold mb-0">Mahasiswa Bimbingan Aktif</h6>
        <a href="{{ route('pembimbing.mahasiswa.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Nama</th><th>NIM</th><th>Program Studi</th><th>Mitra/Instansi</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pengajuans as $p)
                    <tr>
                        <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                        <td>{{ $p->mahasiswa->nim ?? '-' }}</td>
                        <td>{{ $p->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                        <td>{{ $p->mitra->nama_instansi ?? '-' }}</td>
                        <td><span class="badge bg-{{ $p->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ $p->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</span></td>
                        <td><a href="{{ route('pembimbing.mahasiswa.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mahasiswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection