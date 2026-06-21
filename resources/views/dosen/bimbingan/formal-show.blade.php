@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('page-title', 'Detail Bimbingan')

@section('content')
@php
    $statusLabel = $bimbingan->status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $bimbingan->status ?: '-'));
@endphp
<div class="card p-4">
    <table class="table table-borderless">
        <tr><td width="220">Mahasiswa</td><td>{{ $bimbingan->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $bimbingan->tanggal_bimbingan }}</td></tr>
        <tr><td>Topik</td><td>{{ $bimbingan->topik }}</td></tr>
        <tr><td>Status</td><td><span class="badge bg-{{ in_array($bimbingan->status, ['selesai', 'dibalas'], true) ? 'success' : 'secondary' }}">{{ $statusLabel }}</span></td></tr>
        <tr><td>Catatan/Kendala</td><td>{{ $bimbingan->catatan_mahasiswa ?? '-' }}</td></tr>
        <tr><td>Lampiran</td><td>
            @if($bimbingan->lampiran)
                <a href="{{ route('dosen.bimbingan.formal.file', $bimbingan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
            @else
                -
            @endif
        </td></tr>
    </table>
    <form action="{{ route('dosen.bimbingan.formal.reply', $bimbingan->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Balasan/Arahan Dosen</label>
            <textarea name="balasan_dosen" class="form-control" rows="4" required>{{ old('balasan_dosen', $bimbingan->balasan_dosen) }}</textarea>
        </div>
        <div class="alert alert-info py-2">Setelah balasan disimpan, status bimbingan otomatis menjadi <strong>Selesai</strong>.</div>
        <button class="btn btn-primary">Simpan Balasan</button>
        <a href="{{ route('dosen.bimbingan.formal.index', $bimbingan->pengajuan_id) }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
