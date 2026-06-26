@extends('layouts.app')
@section('title', 'Kelayakan Seminar')
@section('page-title', 'Kelayakan Seminar')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Pengiriman kelayakan belum berhasil.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between gap-3 flex-wrap align-items-start">
        <div>
            <h6 class="student-card-title mb-2">Form Kelayakan Seminar</h6>
            <div class="text-muted small">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</div>
            <div class="text-muted small">{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</div>
        </div>
        <a href="{{ route('mahasiswa.seminar.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="card p-4">
    <form action="{{ route('mahasiswa.seminar.kelayakan.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3" novalidate>
        @csrf
        <input type="hidden" name="kelayakan_form_marker" value="submitted">

        <div class="col-12">
            <label class="form-label">Judul Laporan Magang</label>
            <input type="text" name="judul_laporan" class="form-control @error('judul_laporan') is-invalid @enderror" value="{{ old('judul_laporan', $pengajuan->judul_laporan) }}">
            @error('judul_laporan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Laporan Hasil Magang Final</label>
            <input type="file" name="laporan_hasil_magang" class="form-control @error('laporan_hasil_magang') is-invalid @enderror" accept=".pdf">
            <div class="form-text">Format: PDF - Ukuran maksimal 100 MB</div>
            @error('laporan_hasil_magang')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Produk Magang</label>
            <input type="file" name="produk_magang" class="form-control @error('produk_magang') is-invalid @enderror" accept=".pdf,.zip,.rar,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
            <div class="form-text">Format: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, ZIP, RAR, JPG, JPEG, atau PNG - Ukuran maksimal 100 MB</div>
            @error('produk_magang')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Draft Jurnal</label>
            <input type="file" name="draft_jurnal" class="form-control @error('draft_jurnal') is-invalid @enderror" accept=".pdf,.doc,.docx">
            <div class="form-text">Format: PDF, DOC, atau DOCX - Ukuran maksimal 100 MB</div>
            @error('draft_jurnal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Uraian Output Magang</label>
            <textarea name="output_magang" class="form-control @error('output_magang') is-invalid @enderror" rows="4" placeholder="Jelaskan output/hasil magang yang telah dibuat selama magang.">{{ old('output_magang', $kelayakan->output_magang ?? '') }}</textarea>
            @error('output_magang')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Catatan Tambahan</label>
            <textarea name="catatan_mahasiswa" class="form-control @error('catatan_mahasiswa') is-invalid @enderror" rows="2">{{ old('catatan_mahasiswa', $kelayakan->catatan_mahasiswa ?? '') }}</textarea>
            @error('catatan_mahasiswa')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if($kelayakan)
            <div class="col-12">
                <div class="student-action-buttons">
                    <a href="{{ route('mahasiswa.seminar.kelayakan.file', [$kelayakan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Laporan</a>
                    @if($kelayakan->produk_magang)<a href="{{ route('mahasiswa.seminar.kelayakan.file', [$kelayakan->id, 'produk']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Produk</a>@endif
                    @if($kelayakan->draft_jurnal)<a href="{{ route('mahasiswa.seminar.kelayakan.file', [$kelayakan->id, 'jurnal']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Draft Jurnal</a>@endif
                </div>
            </div>
        @endif

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Kirim Kelayakan</button>
        </div>
    </form>
</div>
@endsection
