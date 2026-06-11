@extends('layouts.app')
@section('title', 'Laporan Kukerta')
@section('page-title', 'Laporan Kukerta')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Laporan Kukerta belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Form Laporan Kukerta</h6>
            <div class="text-muted small">Laporan ini dapat dilihat oleh dosen pembimbing Anda.</div>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <form action="{{ route('mahasiswa.laporan-kukerta.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Lokasi/Tempat Kukerta</label>
                <input type="text" name="lokasi_kukerta" class="form-control" value="{{ old('lokasi_kukerta', $laporan->lokasi_kukerta ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Laporan Kukerta PDF {{ $laporan ? '(kosongkan jika tidak diganti)' : '' }}</label>
                <input type="file" name="laporan_kukerta" class="form-control" accept=".pdf" {{ $laporan ? '' : 'required' }}>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Target/Sasaran Kukerta</label>
                <textarea name="target_kukerta" class="form-control" rows="3" required>{{ old('target_kukerta', $laporan->target_kukerta ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Dokumentasi Kukerta</label>
                <input type="file" name="dokumentasi_kukerta[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.zip" multiple>
                <div class="small text-muted mt-1">Boleh unggah beberapa file JPG, PNG, PDF, atau ZIP.</div>
            </div>
        </div>
        <button class="btn btn-primary mt-3">{{ $laporan ? 'Perbarui Laporan' : 'Kirim Laporan' }}</button>
    </form>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Laporan Terkirim</h6>
    @if($laporan)
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Lokasi</div><div class="fw-semibold">{{ $laporan->lokasi_kukerta }}</div></div>
            <div class="col-md-8"><div class="text-muted small">Target/Sasaran</div><div>{{ $laporan->target_kukerta }}</div></div>
            <div class="col-12">
                <a href="{{ route('mahasiswa.laporan-kukerta.file', [$laporan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
            </div>
            <div class="col-12">
                <div class="fw-semibold mb-2">Dokumentasi</div>
                @forelse(($laporan->dokumentasi_kukerta ?? []) as $i => $file)
                    <a href="{{ route('mahasiswa.laporan-kukerta.file', [$laporan->id, 'dokumentasi', $i]) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">Dokumentasi {{ $i + 1 }}</a>
                @empty
                    <span class="text-muted small">Belum ada dokumentasi.</span>
                @endforelse
            </div>
        </div>
    @else
        <div class="text-muted">Belum ada laporan Kukerta.</div>
    @endif
</div>
@endsection