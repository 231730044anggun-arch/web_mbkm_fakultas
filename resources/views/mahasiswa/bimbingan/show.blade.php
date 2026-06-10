@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('page-title', 'Detail Bimbingan')

@section('content')
<div class="card p-4">
    <table class="table table-borderless">
        <tr><td width="220">Tanggal</td><td>{{ $bimbingan->tanggal_bimbingan }}</td></tr>
        <tr><td>Topik</td><td>{{ $bimbingan->topik }}</td></tr>
        <tr><td>Dosen Pembimbing</td><td>{{ $bimbingan->dosen->nama_dosen ?? '-' }}</td></tr>
        <tr><td>Status</td><td><span class="badge bg-secondary">{{ str_replace('_', ' ', $bimbingan->status) }}</span></td></tr>
        <tr><td>Catatan/Kendala</td><td>{{ $bimbingan->catatan_mahasiswa ?? '-' }}</td></tr>
        <tr><td>Balasan Dosen</td><td>{{ $bimbingan->balasan_dosen ?? '-' }}</td></tr>
        <tr><td>Lampiran</td><td>
            @if($bimbingan->lampiran)
                <a href="{{ route('mahasiswa.bimbingan.download', $bimbingan->id) }}" class="btn btn-sm btn-outline-success">Download</a>
            @else
                -
            @endif
        </td></tr>
    </table>
    <a href="{{ route('mahasiswa.bimbingan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
