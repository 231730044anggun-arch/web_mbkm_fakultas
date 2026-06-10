@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('page-title', 'Detail Bimbingan')

@section('content')
@php($p = $bimbingan->pengajuan)
<div class="card p-4">
    <table class="table table-borderless">
        <tr><td width="230">Mahasiswa</td><td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
        <tr><td>NIM</td><td>{{ $p->mahasiswa->nim ?? '-' }}</td></tr>
        <tr><td>Program Studi</td><td>{{ $p->mahasiswa->prodi->nama_prodi ?? '-' }}</td></tr>
        <tr><td>Instansi/Mitra</td><td>{{ $p->mitra->nama_instansi ?? '-' }}</td></tr>
        <tr><td>Dosen Pembimbing</td><td>{{ $bimbingan->dosen->nama_dosen ?? '-' }}</td></tr>
        <tr><td>Pembimbing Lapangan/PIC</td><td>{{ $p->pic_nama ?? $p->mitra->pembimbing_lapangan_nama ?? '-' }}</td></tr>
        <tr><td>Jabatan PIC</td><td>{{ $p->pic_jabatan ?? $p->mitra->pembimbing_lapangan_jabatan ?? '-' }}</td></tr>
        <tr><td>Kontak PIC</td><td>{{ $p->pic_no_hp ?? $p->mitra->pembimbing_lapangan_kontak ?? '-' }}</td></tr>
        <tr><td>Periode</td><td>{{ $p->periode->nama_periode ?? '-' }}</td></tr>
        <tr><td>Tanggal Magang</td><td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td></tr>
        <tr><td>Status Magang</td><td>{{ $p->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</td></tr>
    </table>
    <a href="{{ route('dosen.bimbingan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
