@extends('layouts.app')
@section('title', 'Edit Periode')
@section('page-title', 'Edit Periode')

@section('content')
<div class="card p-4">
    <form action="{{ route('admin.periode.update', $periode->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Periode <span class="text-danger">*</span></label>
                <input type="text" name="nama_periode" class="form-control" value="{{ old('nama_periode', $periode->nama_periode) }}" required>
                @error('nama_periode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $periode->tahun) }}" required>
                @error('tahun') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', optional($periode->tanggal_mulai)->format('Y-m-d') ?: $periode->tanggal_mulai) }}" required>
                @error('tanggal_mulai') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', optional($periode->tanggal_selesai)->format('Y-m-d') ?: $periode->tanggal_selesai) }}" required>
                @error('tanggal_selesai') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="aktif" @selected(old('status', $periode->status) === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status', $periode->status) === 'nonaktif')>Nonaktif</option>
                </select>
                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="{{ route('admin.periode.index') }}" class="btn btn-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
