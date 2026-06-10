@extends('layouts.app')
@section('title', 'Detail Mahasiswa Magang')
@section('page-title', 'Detail Mahasiswa Magang')

@section('content')
<div class="card p-4">
    <table class="table table-borderless">
        <tr><td width="230">Mahasiswa</td><td>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
        <tr><td>NIM</td><td>{{ $pengajuan->mahasiswa->nim ?? '-' }}</td></tr>
        <tr><td>Program Studi</td><td>{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td></tr>
        <tr><td>Periode</td><td>{{ $pengajuan->periode->nama_periode ?? '-' }}</td></tr>
        <tr><td>Tanggal Magang</td><td>{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</td></tr>
        <tr><td>Dosen Pembimbing</td><td>{{ $pengajuan->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}</td></tr>
        <tr><td>Pembimbing Lapangan/PIC</td><td>{{ $pengajuan->pic_nama ?? $pengajuan->mitra->pembimbing_lapangan_nama ?? '-' }}</td></tr>
        <tr><td>Jabatan PIC</td><td>{{ $pengajuan->pic_jabatan ?? $pengajuan->mitra->pembimbing_lapangan_jabatan ?? '-' }}</td></tr>
        <tr><td>Kontak PIC</td><td>{{ $pengajuan->pic_no_hp ?? $pengajuan->mitra->pembimbing_lapangan_kontak ?? '-' }}</td></tr>
        <tr><td>Email PIC</td><td>{{ $pengajuan->pic_email ?? $pengajuan->mitra->pembimbing_lapangan_email ?? '-' }}</td></tr>
        <tr><td>Status Magang</td><td>{{ $pengajuan->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</td></tr>
    </table>
    <a href="{{ route('mitra.pengajuan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
