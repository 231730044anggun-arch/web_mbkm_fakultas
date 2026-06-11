@extends('layouts.app')
@section('title', 'Detail Kelayakan Seminar')
@section('page-title', 'Detail Kelayakan Seminar')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card p-4 mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary mb-3" style="width:max-content">Kembali</a>
    <h6 class="fw-bold mb-3">Data Mahasiswa dan Magang</h6>
    <table class="table table-sm">
        <tr><th>Mahasiswa</th><td>{{ $kelayakan->pengajuan->mahasiswa->nama_lengkap ?? '-' }} / {{ $kelayakan->pengajuan->mahasiswa->nim ?? '-' }}</td></tr>
        <tr><th>Instansi</th><td>{{ $kelayakan->pengajuan->mitra->nama_instansi ?? '-' }}</td></tr>
        <tr><th>Periode</th><td>{{ $kelayakan->pengajuan->tanggal_mulai }} s/d {{ $kelayakan->pengajuan->tanggal_selesai }}</td></tr>
    </table>
    <h6 class="fw-bold mt-4">Bahan Kelayakan</h6>
    <p><strong>Uraian Output Magang:</strong><br>{{ $kelayakan->output_magang }}</p>
    <p><strong>Catatan Mahasiswa:</strong><br>{{ $kelayakan->catatan_mahasiswa ?: 'Tidak ada' }}</p>
    <a href="{{ route(str_starts_with(Route::currentRouteName(), 'dosen.') ? 'dosen.seminar.file' : 'pembimbing.seminar.file', [$kelayakan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
    @if($kelayakan->produk_magang)
    <a href="{{ route(str_starts_with(Route::currentRouteName(), 'dosen.') ? 'dosen.seminar.file' : 'pembimbing.seminar.file', [$kelayakan->id, 'produk']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Produk</a>
    @endif
</div>
<div class="card p-4">
    <h6 class="fw-bold mb-3">Validasi Kelayakan</h6>
    <form action="{{ route(str_starts_with(Route::currentRouteName(), 'dosen.') ? 'dosen.seminar.validasi' : 'pembimbing.seminar.validasi', $kelayakan->id) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select" required><option value="disetujui">Disetujui</option><option value="revisi">Revisi</option><option value="ditolak">Ditolak</option></select></div>
        <div class="col-md-9"><label class="form-label">Catatan</label><textarea name="catatan" class="form-control" rows="2"></textarea><small class="text-muted">Wajib jika revisi atau ditolak.</small></div>
        <div class="col-12 text-end"><button class="btn btn-primary">Simpan Validasi</button></div>
    </form>
</div>
@endsection