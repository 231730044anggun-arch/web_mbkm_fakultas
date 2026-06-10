@extends('layouts.app')
@section('title', 'Detail Mitra')
@section('page-title', 'Detail Mitra')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">{{ $mitra->nama_instansi }}</h6>
        <a href="{{ route('admin.mitra.edit', $mitra->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
    </div>
    <table class="table table-borderless">
        <tr><td width="220">Jenis Mitra</td><td>{{ $mitra->jenis_mitra === 'ber_mou' ? 'Ber-MoU' : 'Non-MoU' }}</td></tr>
        <tr><td>Status Mitra</td><td>{{ ucfirst(str_replace('_', ' ', $mitra->status_mitra_detail ?? 'aktif')) }}</td></tr>
        <tr><td>Status MoU</td><td>{{ ucfirst($mitra->mou_status_label ?? $mitra->status_mou ?? 'tidak') }}</td></tr>
        <tr><td>Nomor MoU</td><td>{{ $mitra->nomor_mou ?? '-' }}</td></tr>
        <tr><td>Tanggal Mulai MoU</td><td>{{ $mitra->tanggal_mulai_mou ?? '-' }}</td></tr>
        <tr><td>Tanggal Berakhir MoU</td><td>{{ $mitra->tanggal_berakhir_mou ?? '-' }}</td></tr>
        <tr>
            <td>File MoU</td>
            <td>
                @if($mitra->file_mou)
                    <a href="{{ asset('storage/'.$mitra->file_mou) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                    <a href="{{ asset('storage/'.$mitra->file_mou) }}" download class="btn btn-sm btn-outline-success">Download</a>
                @else
                    <span class="text-muted">Tidak ada file MoU</span>
                @endif
            </td>
        </tr>
        <tr><td>Email</td><td>{{ $mitra->email ?? '-' }}</td></tr>
        <tr><td>No Telp</td><td>{{ $mitra->no_telp ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td>{{ $mitra->alamat ?? '-' }}</td></tr>
    </table>
    <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary px-4">Kembali</a>
</div>
@endsection
