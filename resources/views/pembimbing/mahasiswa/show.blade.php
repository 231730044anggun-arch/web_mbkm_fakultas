@extends('layouts.app')
@section('title', 'Detail Mahasiswa Bimbingan')
@section('page-title', 'Detail Mahasiswa Bimbingan')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Informasi Mahasiswa Magang</h6>
        <a href="{{ route('pembimbing.mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <tr><th style="width:260px">Nama Mahasiswa</th><td>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
            <tr><th>NIM</th><td>{{ $pengajuan->mahasiswa->nim ?? '-' }}</td></tr>
            <tr><th>Program Studi</th><td>{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td></tr>
            <tr><th>Mitra/Instansi</th><td>{{ $pengajuan->mitra->nama_instansi ?? '-' }}</td></tr>
            <tr><th>Periode</th><td>{{ $pengajuan->periode->nama_periode ?? '-' }}</td></tr>
            <tr><th>Tanggal Magang</th><td>{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</td></tr>
            <tr><th>Dosen Pembimbing</th><td>{{ $pengajuan->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}</td></tr>
            <tr><th>Pembimbing Lapangan</th><td>{{ $pengajuan->pembimbingLapangan->nama ?? $pengajuan->pic_nama ?? '-' }}</td></tr>
            <tr><th>Jabatan</th><td>{{ $pengajuan->pembimbingLapangan->jabatan ?? $pengajuan->pic_jabatan ?? '-' }}</td></tr>
            <tr><th>No HP</th><td>{{ $pengajuan->pembimbingLapangan->no_hp ?? $pengajuan->pic_no_hp ?? '-' }}</td></tr>
            <tr><th>Email</th><td>{{ $pengajuan->pembimbingLapangan->email ?? $pengajuan->pic_email ?? '-' }}</td></tr>
            <tr><th>Status Magang</th><td><span class="badge bg-{{ $pengajuan->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ ucwords(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}</span></td></tr>
        </table>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pembimbing.absensi.index', ['mahasiswa_id' => $pengajuan->mahasiswa_id]) }}" class="btn btn-outline-primary">Lihat Absensi</a>
        <a href="{{ route('pembimbing.logbook.show', $pengajuan->id) }}" class="btn btn-outline-primary">Lihat Logbook</a>
        <a href="{{ route('pembimbing.penilaian.create', $pengajuan->id) }}" class="btn btn-primary">Penilaian Lapangan</a>
    </div>
</div>
@endsection
