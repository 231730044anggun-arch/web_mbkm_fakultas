@extends('layouts.app')
@section('title', 'Tambah Bimbingan')
@section('page-title', 'Tambah Bimbingan')

@section('content')
<div class="card p-4">
    <form action="{{ route('mahasiswa.bimbingan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Pengajuan/Magang Aktif</label>
            <input class="form-control" value="{{ $pengajuan->mitra->nama_instansi ?? '-' }} - {{ $pengajuan->periode->nama_periode ?? '-' }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Dosen Pembimbing</label>
            <input class="form-control" value="{{ $pengajuan->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}" readonly>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Bimbingan</label>
                <input type="date" name="tanggal_bimbingan" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Topik</label>
                <input type="text" name="topik" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Catatan/Kendala Mahasiswa</label>
                <textarea name="catatan_mahasiswa" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Lampiran</label>
                <input type="file" name="lampiran" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
        </div>
        <button class="btn btn-primary mt-3">Kirim Bimbingan</button>
        <a href="{{ route('mahasiswa.bimbingan.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
