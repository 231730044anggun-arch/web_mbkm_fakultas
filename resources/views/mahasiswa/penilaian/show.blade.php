@extends('layouts.app')
@section('title', 'Nilai Magang')
@section('page-title', 'Nilai Magang')

@section('content')
@php
    $nilaiDosenReady = $penilaian?->hasNilaiDosenBaru() ?? false;
    $nilaiPembimbingReady = $penilaian?->hasNilaiPembimbingBaru() ?? false;
    $nilaiLengkap = $seminarValid && $penilaian?->isComplete() && $penilaian->nilai_akhir !== null;

    if (!$seminarValid) {
        $finalMessage = 'Nilai belum tersedia karena Seminar Magang belum diajukan.';
    } elseif (!$nilaiDosenReady && !$nilaiPembimbingReady) {
        $finalMessage = 'Nilai akhir belum tersedia karena nilai dosen pembimbing dan pembimbing lapangan belum lengkap.';
    } elseif (!$nilaiDosenReady) {
        $finalMessage = 'Nilai akhir belum tersedia karena nilai dosen pembimbing belum diinput.';
    } elseif (!$nilaiPembimbingReady) {
        $finalMessage = 'Nilai akhir belum tersedia karena nilai pembimbing lapangan belum diinput.';
    } else {
        $finalMessage = 'Nilai lengkap.';
    }
@endphp

@if(!$seminarValid)
<div class="alert alert-warning">Nilai belum tersedia karena Seminar Magang belum diajukan.</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">Nilai Dosen Pembimbing</h6>
                <span class="badge bg-{{ $nilaiDosenReady ? 'success' : 'secondary' }}">{{ $nilaiDosenReady ? 'Tersedia' : 'Belum tersedia' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Komponen</th><th>Bobot</th><th>Nilai</th></tr></thead>
                    <tbody>
                        <tr><td>Kehadiran dan Disiplin</td><td>15%</td><td>{{ $penilaian?->dosen_kehadiran_disiplin !== null ? number_format($penilaian->dosen_kehadiran_disiplin, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Kinerja dan Sikap Kerja</td><td>30%</td><td>{{ $penilaian?->dosen_kinerja_sikap !== null ? number_format($penilaian->dosen_kinerja_sikap, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Logbook/Kegiatan Harian Magang</td><td>15%</td><td>{{ $penilaian?->dosen_logbook_kegiatan !== null ? number_format($penilaian->dosen_logbook_kegiatan, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Luaran/Hasil Pekerjaan Magang</td><td>20%</td><td>{{ $penilaian?->dosen_luaran !== null ? number_format($penilaian->dosen_luaran, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Laporan Akhir Magang</td><td>20%</td><td>{{ $penilaian?->dosen_laporan_akhir !== null ? number_format($penilaian->dosen_laporan_akhir, 2) : 'Belum ada' }}</td></tr>
                        <tr class="table-light"><th colspan="2">Total Nilai Dosen Pembimbing</th><th>{{ $penilaian?->nilai_dosen_total !== null ? number_format($penilaian->nilai_dosen_total, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan dosen:</strong> {{ $penilaian?->catatan_dosen ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">Nilai Pembimbing Lapangan</h6>
                <span class="badge bg-{{ $nilaiPembimbingReady ? 'success' : 'secondary' }}">{{ $nilaiPembimbingReady ? 'Tersedia' : 'Belum tersedia' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Komponen</th><th>Bobot</th><th>Nilai</th></tr></thead>
                    <tbody>
                        <tr><td>Kehadiran dan Disiplin</td><td>15%</td><td>{{ $penilaian?->pembimbing_kehadiran_disiplin !== null ? number_format($penilaian->pembimbing_kehadiran_disiplin, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Kinerja dan Sikap Kerja</td><td>30%</td><td>{{ $penilaian?->pembimbing_kinerja_sikap !== null ? number_format($penilaian->pembimbing_kinerja_sikap, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Logbook/Kegiatan Harian Magang</td><td>15%</td><td>{{ $penilaian?->pembimbing_logbook_kegiatan !== null ? number_format($penilaian->pembimbing_logbook_kegiatan, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Luaran/Hasil Pekerjaan Magang</td><td>20%</td><td>{{ $penilaian?->pembimbing_luaran !== null ? number_format($penilaian->pembimbing_luaran, 2) : 'Belum ada' }}</td></tr>
                        <tr><td>Laporan Akhir Magang</td><td>20%</td><td>{{ $penilaian?->pembimbing_laporan_akhir !== null ? number_format($penilaian->pembimbing_laporan_akhir, 2) : 'Belum ada' }}</td></tr>
                        <tr class="table-light"><th colspan="2">Total Nilai Pembimbing Lapangan</th><th>{{ $penilaian?->nilai_pembimbing_total !== null ? number_format($penilaian->nilai_pembimbing_total, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan pembimbing:</strong> {{ $penilaian?->catatan_mitra ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4 text-center">
            <h6 class="text-muted mb-2">Nilai Akhir</h6>
            <div class="small text-muted mb-3">Rumus: 50% nilai dosen pembimbing + 50% nilai pembimbing lapangan.</div>
            @if($nilaiLengkap)
                <div class="display-4 fw-bold text-success">{{ number_format($penilaian->nilai_akhir, 2) }}</div>
                <div class="mt-2"><span class="badge bg-success fs-6">Nilai lengkap / Grade {{ $penilaian->grade }}</span></div>
            @else
                <div class="display-6 fw-bold text-muted">-</div>
                <div class="mt-2"><span class="badge bg-warning text-dark fs-6">Nilai belum lengkap</span></div>
                <p class="text-muted mt-3 mb-0">{{ $finalMessage }}</p>
            @endif
        </div>
    </div>
</div>
@endsection