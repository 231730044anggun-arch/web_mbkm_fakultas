@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Jadwal Seminar Mahasiswa Bimbingan</h6>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Mahasiswa</th><th>Judul</th><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $p->judul_laporan ?? '-' }}</td>
                <td>{{ $p->seminar_tanggal ?? '-' }}</td>
                <td>{{ $p->seminar_jam ?? '-' }}</td>
                <td>{{ $p->seminar_ruangan ?? '-' }}</td>
                <td><span class="badge bg-secondary">{{ $p->status_seminar }}</span></td>
                <td><a href="{{ route('dosen.penilaian.create', $p->id) }}" class="btn btn-sm btn-outline-success">Nilai</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada seminar</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $pengajuans->links() }}
</div>
@endsection
