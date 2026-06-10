@extends('layouts.app')
@section('title', 'Detail Pedoman & SOP')
@section('page-title', 'Detail Pedoman & SOP')

@section('content')
@php($hasFile = $pedoman->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($pedoman->file_path))
<div class="card p-4">
    <h6 class="fw-bold mb-3">{{ $pedoman->judul }}</h6>
    <table class="table table-borderless">
        <tr><td width="180">Kategori</td><td>{{ $pedoman->kategori }}</td></tr>
        <tr><td>Tahun</td><td>{{ $pedoman->tahun ?? '-' }}</td></tr>
        <tr>
            <td>File</td>
            <td>
                @if($hasFile)
                    <a href="{{ route('pedoman.preview', $pedoman->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    <a href="{{ route('pedoman.download', $pedoman->id) }}" class="btn btn-sm btn-outline-success">Download File</a>
                @else
                    <span class="text-muted">Tidak ada file</span>
                @endif
            </td>
        </tr>
    </table>
    <a href="{{ route(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' ? 'admin.pedoman.index' : 'pedoman.index') }}" class="btn btn-secondary px-4">Kembali</a>
</div>
@endsection
