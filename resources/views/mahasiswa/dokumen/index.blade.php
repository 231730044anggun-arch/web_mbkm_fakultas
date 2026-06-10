@extends('layouts.app')
@section('title', 'Dokumen')
@section('page-title', 'Dokumen')

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Dokumen belum bisa diproses.</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Upload Dokumen</h6>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <form action="{{ route('mahasiswa.dokumen.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Jenis Dokumen</label>
            <select name="jenis_dokumen" class="form-select" required>
                <option value="surat_diterima" @selected(old('jenis_dokumen') === 'surat_diterima')>Surat Balasan/Bukti Diterima Instansi</option>
                <option value="proposal_magang" @selected(old('jenis_dokumen') === 'proposal_magang')>Proposal Magang</option>
                <option value="laporan" @selected(old('jenis_dokumen') === 'laporan')>Laporan Magang</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">File (PDF/DOC/DOCX, max 5MB)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Upload</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar Semua Dokumen</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Jenis Dokumen</th>
                    <th>Nama Dokumen/File</th>
                    <th>Status</th>
                    <th>Tanggal Upload/Terbit</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokumens as $d)
                @php
                    $hasFile = $d->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($d->file_path);
                    $adminDocs = ['surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar'];
                    $labels = [
                        'surat_diterima' => 'Surat Balasan/Bukti Diterima Instansi',
                        'proposal_magang' => 'Proposal Magang',
                        'laporan' => 'Laporan Magang',
                        'surat_pengantar' => 'Surat Pengantar/Rekomendasi Magang',
                        'surat_keterangan_magang' => 'Surat Keterangan Magang',
                        'sk_magang' => 'SK Magang',
                        'surat_seminar' => 'Surat/SK Seminar',
                    ];
                    $badge = ['pending'=>'warning','valid'=>'success','revisi'=>'danger'];
                @endphp
                <tr>
                    <td>{{ $labels[$d->jenis_dokumen] ?? str_replace('_', ' ', $d->jenis_dokumen) }}</td>
                    <td>
                        <div>{{ $d->nama_file ?? basename($d->file_path ?? '') ?: '-' }}</div>
                        <small class="text-muted">{{ in_array($d->jenis_dokumen, $adminDocs, true) ? 'Terbit/Admin/Sistem' : 'Upload Mahasiswa' }}</small>
                    </td>
                    <td><span class="badge bg-{{ $badge[$d->status_verifikasi] ?? 'secondary' }}">{{ $d->status_verifikasi }}</span></td>
                    <td>{{ $d->tanggal_upload ?? $d->created_at?->format('Y-m-d') }}</td>
                    <td>
                        @if($hasFile)
                            <a href="{{ route('mahasiswa.dokumen.preview', $d->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            <a href="{{ route('mahasiswa.dokumen.download', $d->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                        @else
                            <span class="text-muted small">Dokumen belum tersedia.</span>
                        @endif

                        @if(!in_array($d->jenis_dokumen, $adminDocs, true) && in_array($d->status_verifikasi, ['pending','revisi'], true))
                            <form action="{{ route('mahasiswa.dokumen.update', $d->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                @csrf @method('PUT')
                                <input type="file" name="file" class="form-control form-control-sm mb-1" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <button class="btn btn-sm btn-outline-warning">Ganti</button>
                            </form>
                            <form action="{{ route('mahasiswa.dokumen.destroy', $d->id) }}" method="POST" class="mt-1" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ $d->catatan ?: ($hasFile ? '-' : 'File belum diupload admin.') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada dokumen</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection