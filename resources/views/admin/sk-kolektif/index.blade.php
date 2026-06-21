@extends('layouts.app')
@section('title', 'SK Magang')
@section('page-title', 'SK Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>SK kolektif belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Upload SK Magang</h6>
            <div class="text-muted small">Satu file SK dapat diterbitkan ke banyak mahasiswa dan masuk ke menu Dokumen masing-masing.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sk-kolektif.penempatan') }}" class="btn btn-sm btn-outline-primary">Import Penempatan Magang</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <form action="{{ route('admin.sk-kolektif.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">File SK Magang</label>
                <input type="file" name="file_sk" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Periode Magang</label>
                <select name="periode_id" class="form-select">
                    <option value="">-- Pilih Periode --</option>
                    @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->nama_periode ?? $periode->nama }} {{ $periode->tahun ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" required>
            </div>
            <div class="col-12">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="select_all" value="1" id="selectAllSk">
                    <label class="form-check-label" for="selectAllSk">Pilih semua mahasiswa</label>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th style="width:48px"></th><th>NIM</th><th>Nama</th><th>Program Studi</th><th>Email</th></tr></thead>
                        <tbody>
                        @forelse($mahasiswas as $m)
                            <tr>
                                <td><input class="form-check-input mahasiswa-check" type="checkbox" name="mahasiswa_ids[]" value="{{ $m->id }}"></td>
                                <td>{{ $m->nim }}</td>
                                <td>{{ $m->nama_lengkap }}</td>
                                <td>{{ $m->prodi->nama_prodi ?? $m->program_studi ?? '-' }}</td>
                                <td>{{ $m->email ?? $m->user->email ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <button class="btn btn-primary mt-3">Terbitkan SK Magang</button>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('selectAllSk')?.addEventListener('change', function () {
    document.querySelectorAll('.mahasiswa-check').forEach((checkbox) => checkbox.checked = this.checked);
});
</script>
@endpush
@endsection
