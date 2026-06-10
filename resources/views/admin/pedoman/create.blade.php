@extends('layouts.app')
@section('title', 'Tambah Pedoman')
@section('page-title', 'Tambah Pedoman & SOP')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.pedoman.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-select" required>
                    <option value="Pedoman">Pedoman</option>
                    <option value="SOP">SOP</option>
                    <option value="Template">Template</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tahun</label>
                <input type="number" name="tahun" class="form-control" placeholder="2025">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Upload File</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="{{ route('admin.pedoman.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection