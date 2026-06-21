@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@php
    $formatStatus = fn($status) => ucwords(str_replace('_', ' ', $status ?: '-'));
@endphp
<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Kelayakan Seminar Mahasiswa Bimbingan Lapangan</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th class="align-top">Mahasiswa</th><th class="align-top">Instansi</th><th class="align-top">Status Dosen Pembimbing</th><th class="align-top">Status Pembimbing Lapangan</th><th class="align-top">Aksi</th></tr></thead>
            <tbody>
            @forelse($kelayakans as $k)
                <tr>
                    <td class="align-top">{{ $k->pengajuan->mahasiswa->nama_lengkap ?? '-' }}<div class="small text-muted">{{ $k->pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                    <td class="align-top">{{ $k->pengajuan->mitra->nama_instansi ?? '-' }}</td>
                    <td class="align-top"><span class="badge bg-secondary">{{ $formatStatus($k->status_persetujuan_dosen) }}</span></td>
                    <td class="align-top"><span class="badge bg-secondary">{{ $formatStatus($k->status_persetujuan_pembimbing) }}</span></td>
                    <td class="align-top"><a href="{{ route('pembimbing.seminar.show', $k->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
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
    <table class="table table-hover align-middle">
        <thead class="table-light"><tr><th class="align-top">No</th><th class="align-top">Mahasiswa</th><th class="align-top">Judul</th><th class="align-top">Tanggal</th><th class="align-top">Jam</th><th class="align-top">Ruangan</th><th class="align-top">Status</th><th class="align-top">Aksi</th></tr></thead>
        <tbody>
        @forelse($pengajuans as $i => $p)
            <tr>
                <td class="align-top">{{ $i + 1 }}</td><td class="align-top">{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td><td class="align-top">{{ $p->judul_laporan ?? '-' }}</td><td class="align-top">{{ $p->seminar_tanggal ?? '-' }}</td><td class="align-top">{{ $p->seminar_jam ?? '-' }}</td><td class="align-top">{{ $p->seminar_ruangan ?? '-' }}</td><td class="align-top"><span class="badge bg-secondary">{{ $formatStatus($p->status_seminar) }}</span></td><td class="align-top"><a href="{{ route('pembimbing.penilaian.create', $p->id) }}" class="btn btn-sm btn-outline-success">Nilai</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada seminar</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $pengajuans->links() }}
</div>
@endsection
