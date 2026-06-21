@extends('layouts.app')
@section('title', 'Import Penempatan Magang')
@section('page-title', 'Import Penempatan Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('import_summary'))
    @php($summary = session('import_summary'))
    <div class="card p-4 mb-3 border-success">
        <h6 class="fw-bold mb-3">Ringkasan Import Penempatan</h6>
        <p class="mb-3">Mahasiswa berhasil diproses: <strong>{{ $summary['processed'] ?? 0 }}</strong></p>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="fw-semibold mb-2">Dosen baru dibuat</div>
                @forelse(($summary['dosen'] ?? []) as $i => $item)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $i + 1 }}. {{ $item['name'] ?: '-' }}</div>
                        <div class="small">Email: {{ $item['email'] }}</div>
                        <div class="small">Password sementara: <code>{{ $item['password'] }}</code></div>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada akun dosen baru.</div>
                @endforelse
            </div>
            <div class="col-md-6">
                <div class="fw-semibold mb-2">Pembimbing lapangan baru dibuat</div>
                @forelse(($summary['pembimbing'] ?? []) as $i => $item)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $i + 1 }}. {{ $item['name'] ?: '-' }}</div>
                        <div class="small">Email: {{ $item['email'] }}</div>
                        <div class="small">Password sementara: <code>{{ $item['password'] }}</code></div>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada akun pembimbing lapangan baru.</div>
                @endforelse
            </div>
        </div>
        @if(!empty($summary['notes']))
            <div class="mt-3">
                <div class="fw-semibold mb-1">Catatan</div>
                <ul class="mb-0 small">
                    @foreach(array_slice($summary['notes'], 0, 10) as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="alert alert-warning mt-3 mb-0">Password sementara hanya ditampilkan sekali pada hasil import. User dapat mengubah password melalui menu Profile.</div>
    </div>
@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Import belum bisa diproses.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Import Penempatan Magang</h6>
            <div class="text-muted small">
                CSV ini menghubungkan mahasiswa dengan dosen pembimbing, pembimbing lapangan, instansi, dan tanggal magang.
                Jika tanggal kosong, sistem memakai periode khusus angkatan dari konfigurasi.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sk-kolektif.template-penugasan') }}" class="btn btn-sm btn-outline-primary">Download Template CSV</a>
            <a href="{{ route('admin.sk-kolektif.template-penugasan-xlsx') }}" class="btn btn-sm btn-outline-primary">Download Template XLSX</a>
            <a href="{{ route('admin.sk-kolektif.index') }}" class="btn btn-sm btn-outline-secondary">Upload SK Magang</a>
        </div>
    </div>

    <form action="{{ route('admin.sk-kolektif.import-penugasan') }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-5">
            <label class="form-label fw-semibold">File Penempatan CSV/XLSX</label>
            <input type="file" name="file_penugasan" class="form-control" accept=".csv,.xlsx" required>
            <div class="form-text">Kolom minimal: nim, nama_instansi, nama_dosen_pembimbing, email_dosen_pembimbing, nama_pembimbing_lapangan, email_pembimbing_lapangan, tanggal_mulai_magang, tanggal_selesai_magang.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Periode</label>
            <select name="periode_id" class="form-select">
                <option value="">-- Pilih Periode --</option>
                @foreach($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->nama_periode ?? $periode->nama }} {{ $periode->tahun ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Tanggal Mulai Default</label>
            <input type="date" name="tanggal_mulai" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Tanggal Selesai Default</label>
            <input type="date" name="tanggal_selesai" class="form-control">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Import Penempatan</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
