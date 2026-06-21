@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('page-title', 'Detail Bimbingan')

@section('content')
<div class="card p-4">
    @php
        $statusClass = [
            'menunggu_balasan' => 'warning',
            'dibalas' => 'success',
            'selesai' => 'success',
            'revisi' => 'warning',
            'ditolak' => 'danger',
        ][$bimbingan->status] ?? 'secondary';
        $statusLabel = $bimbingan->status === 'dibalas' ? 'Selesai' : ucwords(str_replace('_', ' ', $bimbingan->status));
    @endphp
    <table class="table table-borderless student-table">
        <tr><td width="220">Tanggal</td><td>{{ $bimbingan->tanggal_bimbingan }}</td></tr>
        <tr><td>Topik</td><td>{{ $bimbingan->topik }}</td></tr>
        <tr><td>Tujuan Bimbingan</td><td>{{ $bimbingan->tujuan_bimbingan === 'pembimbing_lapangan' ? 'Pembimbing Lapangan' : 'Dosen Pembimbing' }}</td></tr>
        <tr><td>Dosen Pembimbing</td><td>{{ $bimbingan->dosen->nama_dosen ?? '-' }}</td></tr>
        <tr><td>Pembimbing Lapangan</td><td>{{ $bimbingan->pembimbingLapangan->nama ?? '-' }}</td></tr>
        <tr><td>Status</td><td><span class="badge bg-{{ $statusClass }} student-badge">{{ $statusLabel }}</span></td></tr>
        <tr><td>Catatan/Kendala</td><td>{{ $bimbingan->catatan_mahasiswa ?? '-' }}</td></tr>
        <tr><td>Balasan</td><td>{{ $bimbingan->balasan_dosen ?? $bimbingan->balasan_pembimbing ?? '-' }}</td></tr>
        <tr><td>Lampiran</td><td>
            @if($bimbingan->lampiran)
                <div class="student-action-buttons"><a href="{{ route('mahasiswa.bimbingan.download', $bimbingan->id) }}" class="btn btn-sm btn-outline-success">Download</a></div>
            @else
                -
            @endif
        </td></tr>
    </table>
    <div class="student-action-buttons"><a href="{{ route('mahasiswa.bimbingan.index') }}" class="btn btn-secondary">Kembali</a></div>
</div>
@endsection
