@extends('layouts.app')
@section('title', 'Edit Mitra')
@section('page-title', 'Edit Mitra')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.mitra.update', $mitra->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Instansi <span class="text-danger">*</span></label>
                <input type="text" name="nama_instansi" class="form-control" value="{{ $mitra->nama_instansi }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kategori Mitra</label>
                <select name="jenis_mitra" class="form-select">
                    <option value="non_mou" {{ $mitra->jenis_mitra === 'non_mou' ? 'selected' : '' }}>Non-MoU</option>
                    <option value="ber_mou" {{ $mitra->jenis_mitra === 'ber_mou' ? 'selected' : '' }}>Ber-MoU</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Jenis Instansi</label>
                <input type="text" name="jenis_instansi" class="form-control" value="{{ $mitra->jenis_instansi }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Bidang Industri</label>
                <input type="text" name="bidang_industri" class="form-control" value="{{ $mitra->bidang_industri }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kota</label>
                <input type="text" name="kota" class="form-control" value="{{ $mitra->kota }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $mitra->email }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">No Telp</label>
                <input type="text" name="no_telp" class="form-control" value="{{ $mitra->no_telp }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status Mitra</label>
                <select name="status_mitra_detail" class="form-select">
                    <option value="aktif" {{ $mitra->status_mitra_detail === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="menunggu_verifikasi" {{ $mitra->status_mitra_detail === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="nonaktif" {{ $mitra->status_mitra_detail === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status MoU</label>
                <select name="status_mou" class="form-select">
                    <option value="tidak" {{ $mitra->status_mou === 'tidak' ? 'selected' : '' }}>Tanpa MoU</option>
                    <option value="aktif" {{ $mitra->status_mou === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="expired" {{ $mitra->status_mou === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Mulai MoU</label>
                <input type="date" name="tanggal_mulai_mou" class="form-control" value="{{ $mitra->tanggal_mulai_mou }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Berakhir MoU</label>
                <input type="date" name="tanggal_berakhir_mou" class="form-control" value="{{ $mitra->tanggal_berakhir_mou }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">File MoU Baru (PDF, max 5MB)</label>
                <input type="file" name="file_mou" class="form-control" accept=".pdf">
                @if($mitra->file_mou)
                    <small class="text-muted d-block mt-1">File lama tetap dipakai jika tidak upload file baru.</small>
                    <a href="{{ route('admin.mitra.mou', $mitra->id) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Lihat File MoU Saat Ini</a>
                @endif
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
