@extends('layouts.app')
@section('title', 'Logbook')
@section('page-title', 'Logbook Magang')

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Logbook belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Tambah Logbook</h6>
            <div class="text-muted small">Isi aktivitas magang dan unggah foto bukti kegiatan.</div>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <form action="{{ route('mahasiswa.logbook.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Tanggal Kegiatan</label><input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal') }}" min="{{ $pengajuan->tanggal_mulai }}" max="{{ $pengajuan->tanggal_selesai }}" required><small class="text-muted">Pilih tanggal sesuai rentang magang: {{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}.</small>@error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}" required>
                @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}" required>
                @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Deskripsi Kegiatan</label>
                <textarea name="kegiatan" class="form-control @error('kegiatan') is-invalid @enderror" rows="4" required>{{ old('kegiatan') }}</textarea>
                @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Output Kegiatan <span class="text-danger">*</span></label>
                <textarea name="output_kegiatan" class="form-control @error('output_kegiatan') is-invalid @enderror" rows="4" required>{{ old('output_kegiatan') }}</textarea>
                @error('output_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Kendala <span class="text-muted small">jika ada</span></label>
                <textarea name="kendala" class="form-control @error('kendala') is-invalid @enderror" rows="3">{{ old('kendala') }}</textarea>
                @error('kendala')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Solusi <span class="text-muted small">jika ada</span></label>
                <textarea name="solusi" class="form-control @error('solusi') is-invalid @enderror" rows="3">{{ old('solusi') }}</textarea>
                @error('solusi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <label class="form-label">Foto Bukti Kegiatan <span class="text-danger">*</span></label>
                <input type="file" name="bukti_foto" class="form-control @error('bukti_foto') is-invalid @enderror" accept="image/*" required>
                @error('bukti_foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 d-grid align-items-end">
                <button type="submit" class="btn btn-primary mt-md-4">Simpan Logbook</button>
            </div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Riwayat Logbook</h6>
            <div class="text-muted small">Logbook yang sudah disimpan akan tampil di sini dan dapat dilihat oleh dosen pembimbing serta mitra terkait.</div>
        </div>
        <form class="row g-2" action="{{ route('mahasiswa.logbook.index', $pengajuan->id) }}" method="GET">
            <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
            <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status')=='disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="revisi" {{ request('status')=='revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
            <div class="col-auto"><a href="{{ route('mahasiswa.logbook.export', $pengajuan->id) }}?from={{ request('from') }}&to={{ request('to') }}&status={{ request('status') }}" class="btn btn-sm btn-outline-secondary">Export PDF</a></div>
        </form>
    </div>

    @if(!empty($missing) && count($missing))
    <div class="alert alert-warning">Logbook mingguan belum lengkap. Anda belum mengisi logbook untuk minggu: {{ implode(', ', $missing) }}.</div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal Kegiatan</th>
                    <th>Jam Kegiatan</th>
                    <th>Deskripsi Kegiatan</th>
                    <th>Output Kegiatan</th>
                    <th>Kendala</th>
                    <th>Solusi</th>
                    <th>Status Dosen</th>
                    <th>Status Mitra</th>
                    <th>Catatan Dosen</th>
                    <th>Catatan Mitra</th>
                    <th>Foto Bukti</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbooks as $l)
                <tr>
                    <td>{{ $l->tanggal }}</td>
                    <td>{{ $l->jam_mulai }} - {{ $l->jam_selesai }}</td>
                    <td>{{ Str::limit($l->kegiatan, 55) }}</td>
                    <td>{{ Str::limit($l->output_kegiatan ?: 'Tidak ada', 55) }}</td>
                    <td>{{ Str::limit($l->kendala ?: 'Tidak ada', 45) }}</td>
                    <td>{{ Str::limit($l->solusi ?: 'Tidak ada', 45) }}</td>
                    <td>
                        @php $badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger']; @endphp
                        <span class="badge bg-{{ $badge[$l->status_dosen ?? 'pending'] ?? 'secondary' }}">{{ $l->status_dosen ?? 'pending' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $badge[$l->status_mitra ?? 'pending'] ?? 'secondary' }}">{{ $l->status_mitra ?? 'pending' }}</span>
                    </td>
                    <td>{{ $l->catatan_dosen ?: 'Tidak ada' }}</td>
                    <td>{{ $l->catatan_mitra ?: 'Tidak ada' }}</td>
                    <td>
                        @if($l->bukti_foto)
                        <a href="{{ route('mahasiswa.logbook.foto.preview', $l->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                        @else
                        <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($l->status_validasi, ['pending','revisi'], true))
                        <a href="{{ route('mahasiswa.logbook.edit', $l->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('mahasiswa.logbook.destroy', $l->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus logbook ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                        @else
                        <span class="text-muted small">Terkunci</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">Belum ada logbook.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logbooks->links() }}
</div>
@endsection

