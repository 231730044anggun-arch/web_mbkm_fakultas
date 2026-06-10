@extends('layouts.app')
@section('title', 'Tambah Mitra')
@section('page-title', 'Tambah Mitra')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.mitra.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Instansi <span class="text-danger">*</span></label>
                <input type="text" name="nama_instansi" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kategori Mitra</label>
                <select name="jenis_mitra" class="form-select">
                    <option value="non_mou">Non-MoU</option>
                    <option value="ber_mou">Ber-MoU</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Jenis Instansi</label>
                <input type="text" name="jenis_instansi" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Bidang Industri</label>
                <input type="text" name="bidang_industri" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kota</label>
                <input type="text" name="kota" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">No Telp</label>
                <input type="text" name="no_telp" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status Mitra</label>
                <select name="status_mitra_detail" class="form-select">
                    <option value="aktif">Aktif</option>
                    <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status MoU</label>
                <select name="status_mou" class="form-select">
                    <option value="tidak">Tanpa MoU</option>
                    <option value="aktif">Aktif</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nomor MoU</label>
                <input type="text" name="nomor_mou" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Mulai MoU</label>
                <input type="date" name="tanggal_mulai_mou" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Berakhir MoU</label>
                <input type="date" name="tanggal_berakhir_mou" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">File MoU (PDF, max 5MB)</label>
                <input type="file" name="file_mou" class="form-control" accept=".pdf">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
