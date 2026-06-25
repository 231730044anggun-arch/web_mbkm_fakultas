@extends('layouts.app')
@section('title', 'Nilai Magang')
@section('page-title', 'Nilai Magang')

@section('content')
<style>
    .assessment-page { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
    .assessment-title { font-size: 20px; font-weight: 700; color: #2f235f; }
    .assessment-card-title { font-size: 18px; font-weight: 700; color: #2f235f; }
    .assessment-summary { background: linear-gradient(135deg, #f6f1ff, #ffffff); border: 1px solid #e2d6ff; border-radius: 16px; }
    .assessment-table th, .assessment-table td { vertical-align: top; font-size: 13.5px; white-space: normal; }
    .assessment-table th { font-weight: 700; color: #4b3a78; }
    .assessment-badge { font-size: 12px; padding: .38rem .55rem; border-radius: 999px; }
    .assessment-file-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .score-hero { background: linear-gradient(135deg, #f2ebff, #ffffff); border: 1px solid #dccfff; border-radius: 18px; }
    .score-hero-label { color: #6b5a89; font-size: 13px; font-weight: 600; }
    .score-hero-number { color: #5b36b2; font-size: clamp(32px, 5vw, 40px); font-weight: 800; line-height: 1; letter-spacing: 0; }
    .score-mini-card { border: 1px solid #e8ddff; border-radius: 14px; padding: 16px; height: 100%; background: #fff; }
    .score-mini-label { color: #6b7280; font-size: 13px; }
    .score-mini-number { color: #4f2da8; font-size: 24px; font-weight: 800; line-height: 1.1; }
    .score-subgrid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
    .score-subitem { background: #f8f5ff; border-radius: 10px; padding: 10px; }
    .formal-file-card { border: 1px solid #e8ddff; border-radius: 14px; padding: 16px; height: 100%; background: #fff; }
    .btn-assessment-outline { border: 1px solid #6f42c1; color: #5b36b2; background: #fff; font-weight: 600; }
    .btn-assessment-outline:hover { background: #f2ebff; border-color: #5b36b2; color: #43208d; }
    @media (max-width: 575.98px) { .score-subgrid { grid-template-columns: 1fr; } }
</style>
@php
    $nilaiDosenReady = $penilaian?->hasNilaiDosenTahap1() ?? false;
    $nilaiPembimbingReady = $penilaian?->hasNilaiPembimbingTahap1() ?? false;
    $nilaiSementara = $penilaian?->nilai_sementara ?? $penilaian?->nilaiAkhirSementara();
    $nilaiSeminarReady = $penilaian?->hasNilaiSeminar() ?? false;
    $nilaiSeminarLengkap = $penilaian?->hasNilaiSeminarLengkap() ?? false;
    $statusKey = $penilaian?->status_nilai ?? 'belum_lengkap';
    $nilaiFinal = $statusKey === 'final' && $penilaian?->nilai_akhir !== null;
    $nilaiAkhirSaatIni = $statusKey === 'akhir_saat_ini' && $penilaian?->nilai_akhir !== null;
    $statusNilai = $penilaian?->statusNilaiLabel() ?? 'Nilai Belum Lengkap';
    $statusBadge = match ($statusKey) {
        'final' => 'success',
        'akhir_saat_ini' => 'info text-dark',
        'sementara' => 'warning text-dark',
        default => 'secondary',
    };
    $finalMessage = $penilaian?->statusNilaiMessage() ?? 'Nilai belum tersedia karena penilaian Dosen Pembimbing dan Pembimbing Lapangan belum lengkap.';
    $mainScoreLabel = $nilaiFinal ? 'Nilai Akhir' : ($nilaiAkhirSaatIni ? 'Nilai Akhir Saat Ini' : 'Nilai Sementara');
    $mainScoreValue = ($nilaiFinal || $nilaiAkhirSaatIni) ? $penilaian?->nilai_akhir : $nilaiSementara;
@endphp

<div class="row g-4 assessment-page">
    <div class="col-12">
        <div class="card p-4 assessment-summary">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h6 class="assessment-title mb-1">Ringkasan Nilai Magang</h6>
                    <div class="text-muted small">Nilai akhir dihitung otomatis oleh sistem.</div>
                </div>
                <span class="badge bg-{{ $statusBadge }} assessment-badge">{{ $statusNilai }}</span>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3"><div class="text-muted small">Nilai Tahap 1 Dosen</div><div class="fw-bold">{{ $penilaian?->nilai_tahap1_dosen !== null ? number_format($penilaian->nilai_tahap1_dosen, 2) : '-' }}</div></div>
                <div class="col-md-3"><div class="text-muted small">Nilai Tahap 1 Pembimbing</div><div class="fw-bold">{{ $penilaian?->nilai_tahap1_pembimbing !== null ? number_format($penilaian->nilai_tahap1_pembimbing, 2) : '-' }}</div></div>
                <div class="col-md-3"><div class="text-muted small">Nilai Seminar Gabungan</div><div class="fw-bold">{{ $penilaian?->nilai_seminar !== null ? number_format($penilaian->nilai_seminar, 2) : '-' }}</div></div>
                <div class="col-md-3"><div class="text-muted small">{{ $mainScoreLabel }}</div><div class="fw-bold {{ $nilaiFinal ? 'text-success' : ($nilaiAkhirSaatIni ? 'text-primary' : 'text-warning') }}">{{ $mainScoreValue !== null ? number_format($mainScoreValue, 2) : '-' }}</div></div>
            </div>
            <div class="mt-3">
                @if($nilaiFinal)
                    <span class="badge bg-success assessment-badge">Grade Final {{ $penilaian->grade }}</span>
                @elseif($nilaiSementara !== null)
                    <span class="badge bg-warning text-dark assessment-badge">Grade Sementara {{ \App\Models\Penilaian::gradeFor($nilaiSementara) }}</span>
                @endif
                <div class="text-muted mt-2">{{ $finalMessage }}</div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4 score-hero">
            @if($nilaiFinal)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="score-hero-label mb-2">Nilai Akhir</div>
                        <div class="score-hero-number">{{ number_format($penilaian->nilai_akhir, 2) }}</div>
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-success assessment-badge mb-2">Nilai Seminar Lengkap</span>
                        <div><span class="badge bg-light text-dark border assessment-badge">Grade {{ $penilaian->grade }}</span></div>
                    </div>
                </div>
                <div class="text-muted small mt-3">{{ $finalMessage }}</div>
            @elseif($nilaiAkhirSaatIni)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="score-hero-label mb-2">Nilai Akhir Saat Ini</div>
                        <div class="score-hero-number">{{ number_format($penilaian->nilai_akhir, 2) }}</div>
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-info text-dark assessment-badge mb-2">Menunggu Penilai Kedua</span>
                        <div><span class="badge bg-light text-dark border assessment-badge">Grade {{ $penilaian->grade }}</span></div>
                    </div>
                </div>
                <div class="text-muted small mt-3">{{ $finalMessage }}</div>
            @elseif($nilaiSementara !== null)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="score-hero-label mb-2">Nilai Sementara</div>
                        <div class="score-hero-number">{{ number_format($nilaiSementara, 2) }}</div>
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-warning text-dark assessment-badge mb-2">Nilai Sementara</span>
                        <div><span class="badge bg-light text-dark border assessment-badge">Grade Sementara {{ \App\Models\Penilaian::gradeFor($nilaiSementara) }}</span></div>
                    </div>
                </div>
                <div class="text-muted small mt-3">{{ $finalMessage }}</div>
            @else
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="score-hero-label mb-2">Nilai Akhir</div>
                        <div class="text-muted fw-semibold">Nilai belum tersedia karena penilaian belum lengkap.</div>
                    </div>
                    <span class="badge bg-secondary assessment-badge">Belum Lengkap</span>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="assessment-card-title mb-0">Penilaian Tahap 1 Dosen Pembimbing</h6>
                <span class="badge bg-{{ $nilaiDosenReady ? 'success' : 'secondary' }} assessment-badge">{{ $nilaiDosenReady ? 'Lengkap' : 'Belum Lengkap' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm assessment-table">
                    <thead class="table-light"><tr><th>Kriteria</th><th>Nilai</th></tr></thead>
                    <tbody>
                        @foreach(\App\Models\Penilaian::tahap1Fields('dosen') as $field => $label)
                            <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td>{{ $penilaian?->{$field} !== null ? number_format($penilaian->{$field}, 2) : 'Belum ada' }}</td></tr>
                        @endforeach
                        <tr class="table-light"><th>Rata-rata Tahap 1 Dosen</th><th>{{ $penilaian?->nilai_tahap1_dosen !== null ? number_format($penilaian->nilai_tahap1_dosen, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan Dosen Pembimbing:</strong> {{ $penilaian?->catatan_dosen ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="assessment-card-title mb-0">Penilaian Tahap 1 Pembimbing Lapangan</h6>
                <span class="badge bg-{{ $nilaiPembimbingReady ? 'success' : 'secondary' }} assessment-badge">{{ $nilaiPembimbingReady ? 'Lengkap' : 'Belum Lengkap' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm assessment-table">
                    <thead class="table-light"><tr><th>Kriteria</th><th>Nilai</th></tr></thead>
                    <tbody>
                        @foreach(\App\Models\Penilaian::tahap1Fields('pembimbing') as $field => $label)
                            <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td>{{ $penilaian?->{$field} !== null ? number_format($penilaian->{$field}, 2) : 'Belum ada' }}</td></tr>
                        @endforeach
                        <tr class="table-light"><th>Rata-rata Tahap 1 Pembimbing</th><th>{{ $penilaian?->nilai_tahap1_pembimbing !== null ? number_format($penilaian->nilai_tahap1_pembimbing, 2) : 'Belum tersedia' }}</th></tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted"><strong>Catatan Pembimbing Lapangan:</strong> {{ $penilaian?->catatan_mitra ?: 'Tidak ada' }}</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4">
            <h6 class="assessment-card-title mb-3">Penilaian Tahap 2 Seminar Hasil Magang</h6>
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="score-mini-card">
                        <div class="score-mini-label mb-2">Dosen Pembimbing</div>
                        <div class="score-mini-number">{{ $penilaian?->nilai_seminar_dosen !== null ? number_format($penilaian->nilai_seminar_dosen, 2) : '-' }}</div>
                        <div class="score-subgrid">
                            <div class="score-subitem"><div class="text-muted small">Laporan</div><div class="fw-semibold">{{ $penilaian?->nilai_laporan_dosen !== null ? number_format($penilaian->nilai_laporan_dosen, 2) : '-' }}</div></div>
                            <div class="score-subitem"><div class="text-muted small">Presentasi</div><div class="fw-semibold">{{ $penilaian?->nilai_presentasi_dosen !== null ? number_format($penilaian->nilai_presentasi_dosen, 2) : '-' }}</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="score-mini-card">
                        <div class="score-mini-label mb-2">Pembimbing Lapangan</div>
                        <div class="score-mini-number">{{ $penilaian?->nilai_seminar_pembimbing !== null ? number_format($penilaian->nilai_seminar_pembimbing, 2) : '-' }}</div>
                        <div class="score-subgrid">
                            <div class="score-subitem"><div class="text-muted small">Laporan</div><div class="fw-semibold">{{ $penilaian?->nilai_laporan_pembimbing !== null ? number_format($penilaian->nilai_laporan_pembimbing, 2) : '-' }}</div></div>
                            <div class="score-subitem"><div class="text-muted small">Presentasi</div><div class="fw-semibold">{{ $penilaian?->nilai_presentasi_pembimbing !== null ? number_format($penilaian->nilai_presentasi_pembimbing, 2) : '-' }}</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="score-mini-card">
                        <div class="score-mini-label mb-2">Nilai Seminar Hasil Magang Gabungan</div>
                        <div class="score-mini-number">{{ $penilaian?->nilai_seminar !== null ? number_format($penilaian->nilai_seminar, 2) : '-' }}</div>
                        <div class="text-muted small mt-3">{{ $penilaian?->seminarStatusLabel() ?? 'Belum Diisi' }}</div>
                    </div>
                </div>
                @if($penilaian?->nama_penguji)<div class="col-md-6"><div class="text-muted small">Penguji</div><div>{{ $penilaian->nama_penguji }}</div></div>@endif
                @if($penilaian?->catatan_seminar)<div class="col-md-6"><div class="text-muted small">Catatan Seminar</div><div>{{ $penilaian->catatan_seminar }}</div></div>@endif
                @unless($penilaian?->nilai_seminar)
                    <div class="col-12"><div class="alert alert-warning py-2 mb-0 small">Nilai seminar hasil magang belum tersedia.</div></div>
                @endunless
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4">
            <h6 class="assessment-card-title mb-3">File Penilaian Formal</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="formal-file-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="fw-semibold">Dosen Pembimbing</div>
                            @if($penilaian?->file_penilaian_formal_dosen)
                                <span class="badge bg-success assessment-badge">Tersedia</span>
                            @else
                                <span class="badge bg-light text-dark border assessment-badge">Belum diunggah</span>
                            @endif
                        </div>
                        @if($penilaian?->file_penilaian_formal_dosen)
                            <div class="assessment-file-actions"><a href="{{ route('mahasiswa.penilaian.file', [$pengajuan->id, 'dosen']) }}" target="_blank" class="btn btn-sm btn-assessment-outline">Lihat/Download</a></div>
                        @else
                            <div class="text-muted small">File penilaian formal dosen belum tersedia.</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="formal-file-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="fw-semibold">Pembimbing Lapangan</div>
                            @if($penilaian?->file_penilaian_formal_pembimbing)
                                <span class="badge bg-success assessment-badge">Tersedia</span>
                            @else
                                <span class="badge bg-light text-dark border assessment-badge">Belum diunggah</span>
                            @endif
                        </div>
                        @if($penilaian?->file_penilaian_formal_pembimbing)
                            <div class="assessment-file-actions"><a href="{{ route('mahasiswa.penilaian.file', [$pengajuan->id, 'pembimbing']) }}" target="_blank" class="btn btn-sm btn-assessment-outline">Lihat/Download</a></div>
                        @else
                            <div class="text-muted small">File penilaian formal pembimbing lapangan belum tersedia.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
