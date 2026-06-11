@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Kelayakan Seminar Mahasiswa Bimbingan Lapangan</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Mahasiswa</th><th>Instansi</th><th>Status Dosen</th><th>Status Pembimbing</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($kelayakans as $k)
                <tr>
                    <td>{{ $k->pengajuan->mahasiswa->nama_lengkap ?? '-' }}<div class="small text-muted">{{ $k->pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                    <td>{{ $k->pengajuan->mitra->nama_instansi ?? '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $k->status_persetujuan_dosen }}</span></td>
                    <td><span class="badge bg-secondary">{{ $k->status_persetujuan_pembimbing }}</span></td>
                    <td><a href="{{ route('pembimbing.seminar.show', $k->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada bahan kelayakan seminar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $kelayakans->links() }}
</div>
<div class="card p-4">
    <h6 class="fw-bold mb-3">Jadwal Seminar Mahasiswa Bimbingan Lapangan</h6>
    <table class="table table-hover">
        <thead class="table-light"><tr><th>No</th><th>Mahasiswa</th><th>Judul</th><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($pengajuans as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td><td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td><td>{{ $p->judul_laporan ?? '-' }}</td><td>{{ $p->seminar_tanggal ?? '-' }}</td><td>{{ $p->seminar_jam ?? '-' }}</td><td>{{ $p->seminar_ruangan ?? '-' }}</td><td><span class="badge bg-secondary">{{ $p->status_seminar }}</span></td><td><a href="{{ route('pembimbing.penilaian.create', $p->id) }}" class="btn btn-sm btn-outline-success">Nilai</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada seminar</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $pengajuans->links() }}
</div>
@endsection