@extends('layouts.app')
@section('title', 'Edit Logbook')
@section('page-title', 'Edit Logbook')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Edit Logbook</h6>
    <form action="{{ route('mahasiswa.logbook.update', $logbook->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Kegiatan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $logbook->tanggal) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', $logbook->jam_mulai) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', $logbook->jam_selesai) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi Kegiatan</label>
                <textarea name="kegiatan" class="form-control" rows="3" required>{{ old('kegiatan', $logbook->kegiatan) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Output Kegiatan</label>
                <textarea name="output_kegiatan" class="form-control" rows="2" required>{{ old('output_kegiatan', $logbook->output_kegiatan) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kendala</label>
                <textarea name="kendala" class="form-control" rows="2">{{ old('kendala', $logbook->kendala) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Solusi</label>
                <textarea name="solusi" class="form-control" rows="2">{{ old('solusi', $logbook->solusi) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Ganti Foto Bukti</label>
                <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                <div class="small text-muted mt-1">Kosongkan jika tidak ingin mengganti foto.</div>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('mahasiswa.logbook.index', $pengajuan->id) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection