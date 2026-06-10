@extends('layouts.app')
@section('title', 'Tambah Periode')
@section('page-title', 'Tambah Periode')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.periode.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Periode <span class="text-danger">*</span></label>
                <input type="text" name="nama_periode" class="form-control" placeholder="cth: Semester Ganjil" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                <input type="number" name="tahun" class="form-control" placeholder="2025" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="{{ route('admin.periode.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection