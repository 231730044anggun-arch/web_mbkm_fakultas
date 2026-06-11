@extends('layouts.app')
@section('title', 'Detail Laporan Kukerta')
@section('page-title', 'Detail Laporan Kukerta')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">{{ $laporan->mahasiswa->nama_lengkap ?? '-' }}</h6>
        <a href="{{ route('dosen.laporan-kukerta.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4"><div class="text-muted small">NIM</div><div class="fw-semibold">{{ $laporan->mahasiswa->nim ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Program Studi</div><div class="fw-semibold">{{ $laporan->mahasiswa->prodi->nama_prodi ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Lokasi Kukerta</div><div class="fw-semibold">{{ $laporan->lokasi_kukerta }}</div></div>
        <div class="col-12"><div class="text-muted small">Target/Sasaran</div><div>{{ $laporan->target_kukerta }}</div></div>
        <div class="col-12">
            <a href="{{ route('dosen.laporan-kukerta.file', [$laporan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Laporan PDF</a>
        </div>
        <div class="col-12">
            <div class="fw-semibold mb-2">Dokumentasi</div>
            @forelse(($laporan->dokumentasi_kukerta ?? []) as $i => $file)
                <a href="{{ route('dosen.laporan-kukerta.file', [$laporan->id, 'dokumentasi', $i]) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">Dokumentasi {{ $i + 1 }}</a>
            @empty
                <span class="text-muted small">Belum ada dokumentasi.</span>
            @endforelse
        </div>
    </div>
</div>
@endsection