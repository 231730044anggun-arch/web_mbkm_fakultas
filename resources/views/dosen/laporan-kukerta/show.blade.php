@extends('layouts.app')
@section('title', 'Detail Laporan Kukerta')
@section('page-title', 'Detail Laporan Kukerta')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">{{ $laporan->mahasiswa->nama_lengkap ?? '-' }}</h6>
        <a href="{{ route('dosen.laporan-kukerta.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    @php
        $deadline = $laporan->mahasiswa?->isAngkatanKhususSkKolektif() ? $laporan->mahasiswa->deadlineLaporanMagang() : null;
    @endphp
    @if($deadline)
        <div class="rounded-3 border px-3 py-2 mb-3 small" style="background:#f4efff;border-color:#ded2ff!important;color:#3b2678;">
            Deadline pengumpulan: <strong>{{ \Illuminate\Support\Carbon::parse($deadline)->locale('id')->translatedFormat('d F Y \p\u\k\u\l H.i') }}</strong>
            @if($laporan->status === 'terlambat')
                <span class="badge bg-danger ms-2">Terlambat</span>
            @endif
        </div>
    @endif
    <div class="row g-3">
        <div class="col-md-4"><div class="text-muted small">NIM</div><div class="fw-semibold">{{ $laporan->mahasiswa->nim ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Program Studi</div><div class="fw-semibold">{{ $laporan->mahasiswa->prodi->nama_prodi ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Lokasi Kukerta</div><div class="fw-semibold">{{ $laporan->lokasi_kukerta }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Tanggal Mulai</div><div class="fw-semibold">{{ optional($laporan->tanggal_mulai_kukerta)->format('d M Y') ?? '-' }}</div></div>
        <div class="col-md-4"><div class="text-muted small">Tanggal Selesai</div><div class="fw-semibold">{{ optional($laporan->tanggal_selesai_kukerta)->format('d M Y') ?? '-' }}</div></div>
        <div class="col-md-4">
            <div class="text-muted small">Status</div>
            <div>
                <span class="badge bg-success">Diterima</span>
                @if($laporan->status === 'terlambat')
                    <span class="badge bg-danger ms-1">Terlambat</span>
                @endif
            </div>
        </div>
        <div class="col-12"><div class="text-muted small">Target/Sasaran</div><div>{{ $laporan->target_kukerta }}</div></div>
        <div class="col-12">
            <div class="text-muted small mb-1">Foto Dokumentasi Kegiatan Kukerta</div>
            <div class="student-action-buttons">
                @forelse(($laporan->foto_dokumentasi_kukerta ?? []) as $i => $foto)
                    <a href="{{ route('dosen.laporan-kukerta.file', [$laporan->id, 'foto', $i]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Foto {{ $i + 1 }}</a>
                @empty
                    <span class="text-muted">Belum ada foto dokumentasi.</span>
                @endforelse
            </div>
        </div>
        <div class="col-12">
            <div class="text-muted small mb-1">Output Kukerta</div>
            <div class="student-action-buttons">
                @if($laporan->output_kukerta_file)
                    <a href="{{ route('dosen.laporan-kukerta.file', [$laporan->id, 'output']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Output File</a>
                @endif
                @if($laporan->output_kukerta_link)
                    <a href="{{ $laporan->output_kukerta_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Buka Output</a>
                @endif
                @if(!$laporan->output_kukerta_file && !$laporan->output_kukerta_link)
                    <span class="text-muted">Tidak ada output tambahan.</span>
                @endif
            </div>
        </div>
        <div class="col-12">
            <a href="{{ route('dosen.laporan-kukerta.file', [$laporan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Laporan PDF</a>
        </div>
    </div>
</div>
@endsection
