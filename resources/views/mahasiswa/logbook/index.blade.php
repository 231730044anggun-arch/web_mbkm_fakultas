@extends('layouts.app')
@section('title', 'Logbook')
@section('page-title', 'Logbook Magang')

@section('content')
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
            <h6 class="student-card-title mb-1">Tambah Logbook</h6>
            <div class="text-muted small">Isi aktivitas magang dan unggah foto bukti kegiatan.</div>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    @php
        $isAngkatanKhusus = $pengajuan->mahasiswa?->isAngkatanKhususSkKolektif();
        $tanggalMulaiMagang = $pengajuan->tanggal_mulai ?: $pengajuan->mahasiswa?->defaultTanggalMulaiMagang();
        $tanggalSelesaiMagang = $pengajuan->tanggal_selesai ?: $pengajuan->mahasiswa?->defaultTanggalSelesaiMagang();
        $penempatanBelumLengkap = $isAngkatanKhusus && !$pengajuan->penempatanLengkap();
    @endphp
    @if($penempatanBelumLengkap)
        <div class="alert alert-warning mb-0">Data penempatan magang Anda belum lengkap. Silakan hubungi admin/fakultas untuk melengkapi dosen pembimbing, pembimbing lapangan, instansi, dan tanggal magang.</div>
    @else
    <form action="{{ route('mahasiswa.logbook.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            @if($isAngkatanKhusus)
                <div class="col-md-4"><label class="form-label">Tanggal Kegiatan</label><input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal') }}" min="{{ $tanggalMulaiMagang }}" max="{{ $tanggalSelesaiMagang }}" required><small class="text-muted">Pilih tanggal sesuai rentang magang: {{ $tanggalMulaiMagang }} s/d {{ $tanggalSelesaiMagang }}.</small>@error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            @else
                <div class="col-md-4"><label class="form-label">Tanggal Kegiatan</label><input type="text" class="form-control" value="{{ now()->toDateString() }}" readonly><small class="text-muted">Tanggal logbook mengikuti tanggal hari ini untuk alur normal.</small></div>
            @endif
            <div class="col-md-4"><label class="form-label">Jam Mulai</label>
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
    @endif
</div>

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="student-card-title mb-1">Riwayat Logbook</h6>
            <div class="text-muted small">Logbook yang sudah disimpan akan tampil di sini dan dapat dilihat oleh dosen pembimbing serta pembimbing lapangan terkait.</div>
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
        <table class="table table-hover student-table">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Tanggal Kegiatan</th>
                    <th class="align-top">Jam Kegiatan</th>
                    <th class="align-top">Deskripsi Kegiatan</th>
                    <th class="align-top">Output Kegiatan</th>
                    <th class="align-top">Kendala</th>
                    <th class="align-top">Solusi</th>
                    <th class="align-top">Status Dosen Pembimbing</th>
                    <th class="align-top">Status Pembimbing Lapangan</th>
                    <th class="align-top">Catatan Dosen Pembimbing</th>
                    <th class="align-top">Catatan Pembimbing Lapangan</th>
                    <th class="align-top">Foto Bukti</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbooks as $l)
                <tr>
                    <td class="align-top">{{ $l->tanggal }}</td>
                    <td class="align-top">{{ $l->jam_mulai }} - {{ $l->jam_selesai }}</td>
                    <td class="align-top">{{ \Illuminate\Support\Str::limit($l->kegiatan, 55) }}</td>
                    <td class="align-top">{{ \Illuminate\Support\Str::limit($l->output_kegiatan ?: 'Tidak ada', 55) }}</td>
                    <td class="align-top">{{ \Illuminate\Support\Str::limit($l->kendala ?: 'Tidak ada', 45) }}</td>
                    <td class="align-top">{{ \Illuminate\Support\Str::limit($l->solusi ?: 'Tidak ada', 45) }}</td>
                    <td class="align-top">
                        @php $badge = ['pending'=>'warning','menunggu'=>'warning','disetujui'=>'success','selesai'=>'success','berjalan'=>'primary','revisi'=>'warning','ditolak'=>'danger','terlambat'=>'danger']; @endphp
                        <span class="badge bg-{{ $badge[$l->status_dosen ?? 'pending'] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $l->status_dosen ?? 'pending')) }}</span>
                    </td>
                    <td class="align-top">
                        <span class="badge bg-{{ $badge[$l->status_mitra ?? 'pending'] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $l->status_mitra ?? 'pending')) }}</span>
                    </td>
                    <td class="align-top">{{ $l->catatan_dosen ?: 'Tidak ada' }}</td>
                    <td class="align-top">{{ $l->catatan_mitra ?: 'Tidak ada' }}</td>
                    <td class="align-top">
                        @if($l->bukti_foto)
                        <a href="{{ route('logbook.bukti.preview', $l->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                        @else
                        <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td class="align-top">
                        @if(in_array($l->status_validasi, ['pending','revisi'], true))
                        <div class="student-action-buttons">
                        <a href="{{ route('mahasiswa.logbook.edit', $l->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('mahasiswa.logbook.destroy', $l->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus logbook ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                        </div>
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
    @if($logbooks->hasMorePages() || $logbooks->currentPage() > 1)
    <div class="mt-3 d-flex justify-content-end gap-2">
        @if($logbooks->onFirstPage())
            <span class="btn btn-sm btn-secondary disabled">Sebelumnya</span>
        @else
            <a href="{{ $logbooks->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">Sebelumnya</a>
        @endif
        @if($logbooks->hasMorePages())
            <a href="{{ $logbooks->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">Berikutnya</a>
        @else
            <span class="btn btn-sm btn-secondary disabled">Berikutnya</span>
        @endif
    </div>
    @endif
</div>
@endsection

