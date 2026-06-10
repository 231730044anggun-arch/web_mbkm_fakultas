@extends('layouts.app')
@section('title', 'Input Penilaian Akademik')
@section('page-title', 'Input Penilaian Akademik')

@section('content')
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Penilaian belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Mahasiswa: {{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
            <div class="text-muted small">NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }} / Program Studi: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
        </div>
        <a href="{{ route('dosen.penilaian.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @unless($canInput)
        <div class="alert alert-warning mb-0">Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.</div>
    @else
    <form action="{{ route('dosen.penilaian.store', $pengajuan->id) }}" method="POST">
        @csrf
        <div class="alert alert-info">Nilai Akademik = (Logbook x 10%) + (Seminar x 30%). Total kontribusi maksimal 40 poin terhadap Nilai Akhir.</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Logbook / Aktivitas Magang <span class="text-muted small">bobot 10%</span></label>
                <input type="number" name="nilai_logbook" class="form-control @error('nilai_logbook') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_logbook', $penilaian->nilai_logbook ?? '') }}" required>
                @error('nilai_logbook')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Seminar / Presentasi Akhir <span class="text-muted small">bobot 30%</span></label>
                <input type="number" name="nilai_presentasi" class="form-control @error('nilai_presentasi') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_presentasi', $penilaian->nilai_presentasi ?? $penilaian->nilai_seminar ?? '') }}" required>
                @error('nilai_presentasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Dosen</label>
                <textarea name="catatan_dosen" class="form-control @error('catatan_dosen') is-invalid @enderror" rows="3">{{ old('catatan_dosen', $penilaian->catatan_dosen ?? $penilaian->catatan ?? '') }}</textarea>
                @error('catatan_dosen')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @if($penilaian)
        <div class="alert alert-info mt-4 mb-0">
            Nilai Akademik saat ini: <strong>{{ $penilaian->nilai_dosen !== null ? number_format($penilaian->nilai_dosen, 2) : 'Belum tersedia' }}</strong>
            @if($penilaian->nilai_akhir !== null)
                / Nilai Akhir: <strong>{{ number_format($penilaian->nilai_akhir, 2) }}</strong> / Grade: <strong>{{ $penilaian->grade }}</strong>
            @else
                / Nilai Akhir belum tersedia karena Nilai Lapangan dan Nilai Akademik belum lengkap.
            @endif
        </div>
        @endif

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">Simpan Nilai Akademik</button>
            <a href="{{ route('dosen.penilaian.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @endunless
</div>
@endsection
