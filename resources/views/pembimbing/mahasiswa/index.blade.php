@extends('layouts.app')
@section('title', 'Mahasiswa Bimbingan')
@section('page-title', 'Mahasiswa Bimbingan')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Daftar Mahasiswa Bimbingan</h6>
            <div class="text-muted small">Hanya mahasiswa yang ditugaskan kepada Anda sebagai Pembimbing Lapangan.</div>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/NIM..." value="{{ request('search') }}">
            <button class="btn btn-sm btn-outline-primary">Cari</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>No</th><th>Nama</th><th>NIM</th><th>Program Studi</th><th>Periode</th><th>Mitra/Instansi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $i => $p)
                <tr>
                    <td>{{ $pengajuans->firstItem() + $i }}</td>
                    <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $p->mahasiswa->nim ?? '-' }}</td>
                    <td>{{ $p->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $p->periode->nama_periode ?? '-' }}</td>
                    <td>{{ $p->mitra->nama_instansi ?? '-' }}</td>
                    <td><span class="badge bg-{{ $p->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ $p->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</span></td>
                    <td><a href="{{ route('pembimbing.mahasiswa.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mahasiswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection