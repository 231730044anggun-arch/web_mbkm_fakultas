@extends('layouts.app')
@section('title', 'Penilaian')
@section('page-title', 'Penilaian Akademik')

@section('content')
<style>
    .assessment-page { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
    .assessment-page .section-title { font-size: 18px; font-weight: 700; color: #2f235f; }
    .assessment-table th, .assessment-table td { vertical-align: top; font-size: 13.5px; white-space: normal; }
    .assessment-table th { font-weight: 700; color: #4b3a78; }
    .assessment-badge { font-size: 12px; padding: .38rem .55rem; border-radius: 999px; }
    .assessment-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
</style>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@php
    $formatStatus = fn($status) => ucwords(str_replace('_', ' ', $status ?: 'belum'));
@endphp

<div class="card p-4 assessment-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="section-title mb-1">Daftar Penilaian Akademik Mahasiswa Bimbingan</h6>
            <div class="text-muted small">Kelola nilai Tahap 1 dan Tahap 2 mahasiswa bimbingan.</div>
        </div>
    </div>

    <div class="table-responsive" style="overflow-x:auto;">
        <table class="table table-hover assessment-table">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Mahasiswa</th>
                    <th class="align-top">NIM</th>
                    <th class="align-top">Program Studi</th>
                    <th class="align-top">Tempat Magang/Mitra</th>
                    <th class="align-top">Status Nilai</th>
                    <th class="align-top">Nilai Sementara Saya</th>
                    <th class="align-top">Nilai Final</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $pengajuan)
                @php
                    $canInput = true;
                @endphp
                <tr>
                    <td class="align-top">
                        <div class="fw-semibold">{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</div>
                        <div class="small text-muted">{{ $formatStatus($pengajuan->status_seminar ?: 'belum') }}</div>
                    </td>
                    <td class="align-top">{{ $pengajuan->mahasiswa->nim ?? '-' }}</td>
                    <td class="align-top">{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td class="align-top">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td class="align-top">
                        @php
                            $nilaiStatus = $pengajuan->penilaian?->status_nilai ?? 'belum_lengkap';
                            $nilaiBadge = match ($nilaiStatus) {
                                'final' => 'success',
                                'akhir_saat_ini' => 'info text-dark',
                                'sementara' => 'warning text-dark',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $nilaiBadge }} assessment-badge">{{ $pengajuan->penilaian?->statusNilaiLabel() ?? 'Nilai Belum Lengkap' }}</span>
                    </td>
                    <td class="align-top">{{ $pengajuan->penilaian?->nilai_tahap1_dosen !== null ? number_format($pengajuan->penilaian->nilai_tahap1_dosen, 2) : '-' }}</td>
                    <td class="align-top">
                        @if($pengajuan->penilaian?->nilai_akhir !== null)
                            <div class="fw-semibold text-success">{{ number_format($pengajuan->penilaian->nilai_akhir, 2) }}</div>
                            <div class="small text-muted">Grade {{ $pengajuan->penilaian->grade }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="align-top">
                        <div class="assessment-actions">
                            <a href="{{ route('dosen.penilaian.create', $pengajuan->id) }}" class="btn btn-sm btn-primary">{{ $pengajuan->penilaian ? 'Edit Nilai' : 'Input Nilai' }}</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mahasiswa yang dapat dinilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection
