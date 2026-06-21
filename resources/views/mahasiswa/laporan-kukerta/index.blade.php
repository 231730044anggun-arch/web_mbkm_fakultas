@extends('layouts.app')
@section('title', 'Laporan Kukerta')
@section('page-title', 'Laporan Kukerta')

@section('content')
@if($errors->any())
<div class="alert alert-danger">
    <strong>Laporan Kukerta belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="student-card-title mb-1">Form Laporan Kukerta</h6>
            <div class="text-muted small">Laporan ini dapat dilihat oleh dosen pembimbing Anda.</div>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    @if($deadline)
        <div class="rounded-3 border px-3 py-2 mb-3 small" style="background:#f4efff;border-color:#ded2ff!important;color:#3b2678;">
            Deadline pengumpulan: <strong>{{ \Illuminate\Support\Carbon::parse($deadline)->locale('id')->translatedFormat('d F Y \p\u\k\u\l H.i') }}</strong>
            @if($melewatiDeadline && !$laporan)
                <span class="badge bg-danger student-badge ms-2">Terlambat</span>
            @elseif($laporan?->status === 'terlambat')
                <span class="badge bg-danger student-badge ms-2">Terlambat</span>
            @endif
        </div>
    @endif
    <form action="{{ route('mahasiswa.laporan-kukerta.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Lokasi/Tempat Kukerta</label>
                <input type="text" name="lokasi_kukerta" class="form-control" value="{{ old('lokasi_kukerta', $laporan->lokasi_kukerta ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Mulai Kukerta</label>
                <input type="date" name="tanggal_mulai_kukerta" class="form-control" value="{{ old('tanggal_mulai_kukerta', optional($laporan?->tanggal_mulai_kukerta)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Selesai Kukerta</label>
                <input type="date" name="tanggal_selesai_kukerta" class="form-control" value="{{ old('tanggal_selesai_kukerta', optional($laporan?->tanggal_selesai_kukerta)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Laporan Kukerta PDF {{ $laporan ? '(kosongkan jika tidak diganti)' : '' }}</label>
                <input type="file" name="laporan_kukerta" class="form-control" accept=".pdf" {{ $laporan ? '' : 'required' }}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Foto Dokumentasi Kegiatan Kukerta {{ $laporan && count($laporan->foto_dokumentasi_kukerta ?? []) ? '(kosongkan jika tidak diganti)' : '' }}</label>
                <input type="file" name="foto_dokumentasi_kukerta[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple {{ $laporan && count($laporan->foto_dokumentasi_kukerta ?? []) ? '' : 'required' }}>
                <small class="text-muted">Bisa upload lebih dari satu foto. Format: JPG, JPEG, PNG, WEBP.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Output Kukerta File <span class="text-muted small">(opsional)</span></label>
                <input type="file" name="output_kukerta_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Output Kukerta Link <span class="text-muted small">(opsional)</span></label>
                <input type="url" name="output_kukerta_link" class="form-control" value="{{ old('output_kukerta_link', $laporan->output_kukerta_link ?? '') }}" placeholder="https://contoh.com/output-kukerta">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Target/Sasaran Kukerta</label>
                <textarea name="target_kukerta" class="form-control" rows="3" required>{{ old('target_kukerta', $laporan->target_kukerta ?? '') }}</textarea>
            </div>
        </div>
        <button class="btn btn-primary mt-3">{{ $laporan ? 'Perbarui Laporan' : 'Kirim Laporan' }}</button>
    </form>
</div>

<div class="card p-4">
    <h6 class="student-card-title mb-3">Laporan Terkirim</h6>
    @if($laporan)
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Lokasi</div><div class="fw-semibold">{{ $laporan->lokasi_kukerta }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Tanggal Mulai</div><div class="fw-semibold">{{ optional($laporan->tanggal_mulai_kukerta)->format('d M Y') ?? '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Tanggal Selesai</div><div class="fw-semibold">{{ optional($laporan->tanggal_selesai_kukerta)->format('d M Y') ?? '-' }}</div></div>
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <div>
                    <span class="badge bg-success student-badge">Terkirim</span>
                    @if($laporan->status === 'terlambat')
                        <span class="badge bg-danger student-badge ms-1">Terlambat</span>
                    @endif
                </div>
            </div>
            <div class="col-12"><div class="text-muted small">Target/Sasaran</div><div>{{ $laporan->target_kukerta }}</div></div>
            <div class="col-12">
                <div class="text-muted small mb-1">Foto Dokumentasi Kegiatan Kukerta</div>
                <div class="student-action-buttons">
                    @forelse(($laporan->foto_dokumentasi_kukerta ?? []) as $i => $foto)
                        <a href="{{ route('mahasiswa.laporan-kukerta.file', [$laporan->id, 'foto', $i]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Foto {{ $i + 1 }}</a>
                    @empty
                        <span class="text-muted">Belum ada foto dokumentasi.</span>
                    @endforelse
                </div>
            </div>
            <div class="col-12">
                <div class="text-muted small mb-1">Output Kukerta</div>
                <div class="student-action-buttons">
                    @if($laporan->output_kukerta_file)
                        <a href="{{ route('mahasiswa.laporan-kukerta.file', [$laporan->id, 'output']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Output File</a>
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
                <div class="student-action-buttons">
                    <a href="{{ route('mahasiswa.laporan-kukerta.file', [$laporan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
                </div>
            </div>
        </div>
    @else
        <div class="text-muted">Belum ada laporan Kukerta.</div>
    @endif
</div>
@endsection
