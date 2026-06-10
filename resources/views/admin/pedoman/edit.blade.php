@extends('layouts.app')
@section('title', 'Edit Pedoman')
@section('page-title', 'Edit Pedoman & SOP')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.pedoman.update', $pedoman->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control" value="{{ old('judul', $pedoman->judul) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-select" required>
                    <option value="Pedoman" @selected(old('kategori', $pedoman->kategori) === 'Pedoman')>Pedoman</option>
                    <option value="SOP" @selected(old('kategori', $pedoman->kategori) === 'SOP')>SOP</option>
                    <option value="Template" @selected(old('kategori', $pedoman->kategori) === 'Template')>Template</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $pedoman->tahun) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Ganti File</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                @if($pedoman->file_path)
                    <div class="text-muted small mt-1">File saat ini: {{ basename($pedoman->file_path) }}</div>
                @endif
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="{{ route('admin.pedoman.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
