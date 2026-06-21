@extends('layouts.app')
@section('title', 'Dokumen')
@section('page-title', 'Dokumen')

@section('content')
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
        <h6 class="student-card-title mb-0">Upload Dokumen</h6>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <form action="{{ route('mahasiswa.dokumen.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Jenis Dokumen</label>
            <select name="jenis_dokumen" class="form-select" required>
                <option value="surat_diterima" @selected(old('jenis_dokumen') === 'surat_diterima')>Surat Balasan/Bukti Diterima Instansi</option>
                <option value="proposal_magang" @selected(old('jenis_dokumen') === 'proposal_magang')>Proposal Magang</option>
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
    <h6 class="student-card-title mb-3">Daftar Semua Dokumen</h6>
    <div class="table-responsive">
        <table class="table table-hover student-table">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Jenis Dokumen</th>
                    <th class="align-top">Nama Dokumen/File</th>
                    <th class="align-top">Status</th>
                    <th class="align-top">Tanggal Upload/Terbit</th>
                    <th class="align-top">Aksi</th>
                    <th class="align-top">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokumens as $d)
                @php
                    $hasFile = (bool) $d->file_path;
                    $systemDocs = ['surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar','laporan_hasil_magang','produk_magang','laporan_kukerta','draft_jurnal','output_kukerta','file_penilaian_formal_dosen','file_penilaian_formal_pembimbing','laporan'];
                    $labels = [
                        'surat_diterima' => 'Surat Balasan/Bukti Diterima Instansi',
                        'proposal_magang' => 'Proposal Magang',
                        'laporan' => 'Laporan Seminar Magang',
                        'laporan_hasil_magang' => 'Laporan Hasil Magang',
                        'produk_magang' => 'Produk Magang',
                        'laporan_kukerta' => 'Laporan Kukerta',
                        'draft_jurnal' => 'Draft Jurnal',
                        'output_kukerta' => 'Output Kukerta',
                        'file_penilaian_formal_dosen' => 'File Penilaian Formal Dosen Pembimbing',
                        'file_penilaian_formal_pembimbing' => 'File Penilaian Formal Pembimbing Lapangan',
                        'surat_pengantar' => 'Surat Pengantar/Rekomendasi Magang',
                        'surat_keterangan_magang' => 'Surat Keterangan Magang',
                        'sk_magang' => 'SK Magang',
                        'surat_seminar' => 'Surat/SK Seminar',
                    ];
                    $badge = ['pending'=>'warning','valid'=>'success','revisi'=>'danger'];
                    $manualArchiveDocs = ['surat_diterima', 'proposal_magang'];
                    $isManualArchive = in_array($d->jenis_dokumen, $manualArchiveDocs, true);
                    $statusLabel = $d->status_verifikasi ? ucwords(str_replace('_', ' ', $d->status_verifikasi)) : '-';
                @endphp
                <tr>
                    <td class="align-top">{{ $labels[$d->jenis_dokumen] ?? ucwords(str_replace('_', ' ', $d->jenis_dokumen)) }}</td>
                    <td class="align-top">
                        <div>{{ $d->nama_file ?? basename($d->file_path ?? '') ?: '-' }}</div>
                        <small class="text-muted">{{ in_array($d->jenis_dokumen, $systemDocs, true) ? 'Terbit/Admin/Sistem/Modul Terkait' : 'Upload Mahasiswa' }}</small>
                    </td>
                    <td class="align-top">
                        @if($isManualArchive)
                            <span class="text-muted small">-</span>
                        @else
                            <span class="badge bg-{{ $badge[$d->status_verifikasi] ?? 'secondary' }} student-badge">{{ $statusLabel }}</span>
                        @endif
                    </td>
                    <td class="align-top">{{ $d->tanggal_upload ?? $d->created_at?->format('Y-m-d') }}</td>
                    <td class="align-top">
                        <div class="student-action-buttons">
                        @if($hasFile)
                            <a href="{{ route('mahasiswa.dokumen.preview', $d->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            <a href="{{ route('mahasiswa.dokumen.download', $d->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                        @else
                            <span class="text-muted small">Dokumen belum tersedia.</span>
                        @endif

                        @if($isManualArchive)
                            <form action="{{ route('mahasiswa.dokumen.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                        </div>
                    </td>
                    <td class="align-top">{{ $d->catatan ?: ($hasFile ? '-' : 'File belum tersedia.') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada dokumen</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
