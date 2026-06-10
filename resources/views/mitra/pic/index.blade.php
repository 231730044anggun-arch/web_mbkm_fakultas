@extends('layouts.app')
@section('title', 'Pembimbing Lapangan/PIC')
@section('page-title', 'Pembimbing Lapangan/PIC')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar Pembimbing Lapangan/PIC</h6>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Nama PIC</th><th>Jabatan/Divisi</th><th>No HP</th><th>Email</th><th>Jumlah Mahasiswa</th><th>Mahasiswa</th></tr>
        </thead>
        <tbody>
            @forelse($picGroups as $nama => $items)
            @php($first = $items->first())
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $nama }}</td>
                <td>{{ $first->pic_jabatan ?? '-' }}</td>
                <td>{{ $first->pic_no_hp ?? '-' }}</td>
                <td>{{ $first->pic_email ?? '-' }}</td>
                <td>{{ $items->count() }}</td>
                <td>{{ $items->pluck('mahasiswa.nama_lengkap')->filter()->implode(', ') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada data PIC</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
