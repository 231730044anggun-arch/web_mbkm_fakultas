@extends('layouts.app')
@section('title', 'Nilai Magang')
@section('page-title', 'Nilai Magang')

@section('content')
@php
    $nilaiLapanganReady = $penilaian?->hasNilaiLapangan() ?? false;
    $nilaiAkademikReady = $penilaian?->hasNilaiAkademik() ?? false;
    $nilaiLengkap = $seminarValid && $penilaian?->isComplete() && $penilaian->nilai_akhir !== null;

    if (!$seminarValid) {
        $finalMessage = 'Nilai belum tersedia karena Seminar Magang belum diajukan.';
    } elseif (!$nilaiLapanganReady && !$nilaiAkademikReady) {
        $finalMessage = 'Nilai Akhir belum tersedia karena Nilai Lapangan dan Nilai Akademik belum lengkap.';
    } elseif (!$nilaiLapanganReady) {
        $finalMessage = 'Nilai Akhir belum tersedia karena Nilai Lapangan belum diinput.';
    } elseif (!$nilaiAkademikReady) {
        $finalMessage = 'Nilai Akhir belum tersedia karena Nilai Akademik belum diinput.';
    } else {
        $finalMessage = 'Nilai lengkap.';
    }
@endphp

@if(!$seminarValid)
<div class="alert alert-warning">Nilai belum tersedia karena Seminar Magang belum diajukan.</div>
@endif

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">Nilai Lapangan</h6>
                <span class="badge bg-{{ $nilaiLapanganReady ? 'success' : 'secondary' }}">{{ $nilaiLapanganReady ? 'Tersedia' : 'Belum tersedia' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Komponen</th><th>Bobot</th><th>Nilai</th><th>Kontribusi</th></tr></thead>
                    <tbody>
                        <tr><td>Absensi / Kehadiran</td><td>10%</td><td>{{ $penilaian?->nilai_absensi !== null ? number_format($penilaian->nilai_absensi, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_absensi !== null ? number_format($penilaian->nilai_absensi * 0.10, 2) : '-' }}</td></tr>
                        <tr><td>Sikap dan Etika Kerja</td><td>15%</td><td>{{ $penilaian?->nilai_sikap_etika !== null ? number_format($penilaian->nilai_sikap_etika, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_sikap_etika !== null ? number_format($penilaian->nilai_sikap_etika * 0.15, 2) : '-' }}</td></tr>
                        <tr><td>Teamwork / Kerja Sama Tim</td><td>15%</td><td>{{ $penilaian?->nilai_teamwork !== null ? number_format($penilaian->nilai_teamwork, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_teamwork !== null ? number_format($penilaian->nilai_teamwork * 0.15, 2) : '-' }}</td></tr>
                        <tr><td>Kedisiplinan dan Tanggung Jawab</td><td>20%</td><td>{{ $penilaian?->nilai_disiplin_tanggung_jawab !== null ? number_format($penilaian->nilai_disiplin_tanggung_jawab, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_disiplin_tanggung_jawab !== null ? number_format($penilaian->nilai_disiplin_tanggung_jawab * 0.20, 2) : '-' }}</td></tr>
                        <tr class="table-light"><th colspan="3">Total Nilai Lapangan</th><th>{{ $penilaian?->nilai_lapangan !== null ? number_format($penilaian->nilai_lapangan, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan mitra:</strong> {{ $penilaian?->catatan_mitra ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">Nilai Akademik</h6>
                <span class="badge bg-{{ $nilaiAkademikReady ? 'success' : 'secondary' }}">{{ $nilaiAkademikReady ? 'Tersedia' : 'Belum tersedia' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Komponen</th><th>Bobot</th><th>Nilai</th><th>Kontribusi</th></tr></thead>
                    <tbody>
                        <tr><td>Logbook / Aktivitas Magang</td><td>10%</td><td>{{ $penilaian?->nilai_logbook !== null ? number_format($penilaian->nilai_logbook, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_logbook !== null ? number_format($penilaian->nilai_logbook * 0.10, 2) : '-' }}</td></tr>
                        <tr><td>Seminar / Presentasi Akhir</td><td>30%</td><td>{{ $penilaian?->nilai_presentasi !== null ? number_format($penilaian->nilai_presentasi, 2) : 'Belum ada' }}</td><td>{{ $penilaian?->nilai_presentasi !== null ? number_format($penilaian->nilai_presentasi * 0.30, 2) : '-' }}</td></tr>
                        <tr class="table-light"><th colspan="3">Total Nilai Akademik</th><th>{{ $penilaian?->nilai_dosen !== null ? number_format($penilaian->nilai_dosen, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan dosen:</strong> {{ $penilaian?->catatan_dosen ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4 text-center">
            <h6 class="text-muted mb-2">Nilai Akhir</h6>
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
