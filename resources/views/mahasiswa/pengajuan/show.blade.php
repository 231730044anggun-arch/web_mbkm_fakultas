@extends('layouts.app')
@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan')

@section('content')
@php
    $isSuratPengantar = $pengajuan->jenis_pengajuan === 'surat_pengantar';
    $isSkMagang = $pengajuan->jenis_pengajuan === 'surat_keterangan';
    $hasFile = fn($dokumen) => $dokumen
        && $dokumen->file_path
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($dokumen->file_path);
    $suratPengantar = $pengajuan->dokumens
        ->where('jenis_dokumen', 'surat_pengantar')
        ->where('status_verifikasi', 'valid')
        ->first();
    $suratKeterangan = $pengajuan->dokumens
        ->where('jenis_dokumen', 'surat_keterangan_magang')
        ->where('status_verifikasi', 'valid')
        ->first();
    $skMagang = $pengajuan->dokumens
        ->where('jenis_dokumen', 'sk_magang')
        ->where('status_verifikasi', 'valid')
        ->first();
    $suratBalasan = $pengajuan->dokumens->where('jenis_dokumen', 'surat_diterima')->first();
    $proposalMagang = $pengajuan->dokumens->where('jenis_dokumen', 'proposal_magang')->first();
@endphp

<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Informasi Pengajuan</h6>
    <table class="table table-borderless">
        <tr><td width="230">Jenis Pengajuan</td><td>{{ $isSkMagang ? 'SK Magang' : 'Surat Pengantar/Rekomendasi Magang' }}</td></tr>
        <tr><td>Periode</td><td>{{ $pengajuan->periode->nama_periode ?? '-' }}</td></tr>
        <tr><td>Status</td><td>
            @php $badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger','berjalan'=>'info','selesai'=>'secondary','dibatalkan'=>'dark']; @endphp
            <span class="badge bg-{{ $badge[$pengajuan->status_pengajuan] ?? 'secondary' }}">{{ $pengajuan->status_pengajuan }}</span>
        </td></tr>
        @if(in_array($pengajuan->status_pengajuan, ['pending','revisi'], true))
            <tr><td>Aksi</td><td>
                <form action="{{ route('mahasiswa.pengajuan.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Batalkan Pengajuan</button>
                </form>
            </td></tr>
        @endif
        <tr><td>Instansi Tujuan</td><td>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td></tr>
        <tr><td>Jenis Mitra</td><td>{{ $pengajuan->jenis_mitra ? str_replace('_', ' ', $pengajuan->jenis_mitra) : '-' }}</td></tr>
        <tr><td>Alamat Instansi</td><td>{{ $pengajuan->mitra->alamat ?? $pengajuan->alamat_instansi_manual ?? '-' }}</td></tr>
        <tr><td>Kota Instansi</td><td>{{ $pengajuan->mitra->kota ?? $pengajuan->kota_instansi_manual ?? '-' }}</td></tr>
        <tr><td>Bidang Instansi</td><td>{{ $pengajuan->mitra->bidang_industri ?? '-' }}</td></tr>
        <tr><td>Email Instansi</td><td>{{ $pengajuan->mitra->email ?? '-' }}</td></tr>
        <tr><td>Nomor Telepon Instansi</td><td>{{ $pengajuan->mitra->no_telp ?? '-' }}</td></tr>
        <tr><td>Posisi/Bidang Magang</td><td>{{ $pengajuan->posisi_magang }}</td></tr>
        <tr><td>Tanggal Mulai</td><td>{{ $pengajuan->tanggal_mulai ?? '-' }}</td></tr>
        <tr><td>Tanggal Selesai</td><td>{{ $pengajuan->tanggal_selesai ?? '-' }}</td></tr>
        <tr><td>Rencana/Deskripsi Kegiatan</td><td>{{ $pengajuan->deskripsi_kegiatan ?? '-' }}</td></tr>
        @if($isSkMagang)
            <tr><td>Nomor Surat Balasan</td><td>{{ $pengajuan->nomor_surat_balasan ?? '-' }}</td></tr>
            <tr><td>Tanggal Surat Balasan</td><td>{{ $pengajuan->tanggal_surat_balasan ?? '-' }}</td></tr>
            <tr><td>Pembimbing Lapangan</td><td>{{ $pengajuan->pembimbingLapangan->nama ?? $pengajuan->pic_nama ?? $pengajuan->mitra->pembimbing_lapangan_nama ?? '-' }}</td></tr>
            <tr><td>Jabatan Pembimbing</td><td>{{ $pengajuan->pembimbingLapangan->jabatan ?? $pengajuan->pic_jabatan ?? $pengajuan->mitra->pembimbing_lapangan_jabatan ?? '-' }}</td></tr>
            <tr><td>No HP Pembimbing</td><td>{{ $pengajuan->pembimbingLapangan->no_hp ?? $pengajuan->pic_no_hp ?? $pengajuan->mitra->pembimbing_lapangan_kontak ?? '-' }}</td></tr>
            <tr><td>Email Pembimbing</td><td>{{ $pengajuan->pembimbingLapangan->email ?? $pengajuan->pic_email ?? $pengajuan->mitra->pembimbing_lapangan_email ?? '-' }}</td></tr>
            <tr><td>Surat Balasan/Bukti Diterima</td><td>
                @if($hasFile($suratBalasan))
                    <a href="{{ route('mahasiswa.dokumen.preview', $suratBalasan->id) }}" target="_blank">Preview</a>
                    <span class="text-muted">({{ $suratBalasan->status_verifikasi }})</span>
                @else
                    -
                @endif
            </td></tr>
            <tr><td>Proposal Magang</td><td>
                @if($hasFile($proposalMagang))
                    <a href="{{ route('mahasiswa.dokumen.preview', $proposalMagang->id) }}" target="_blank">Preview</a>
                    <span class="text-muted">({{ $proposalMagang->status_verifikasi }})</span>
                @else
                    -
                @endif
            </td></tr>
        @endif
        @if($pengajuan->catatan_admin)
            <tr><td>Catatan Admin</td><td>{{ $pengajuan->catatan_admin }}</td></tr>
        @endif
        @if(!$isSuratPengantar)
            <tr><td>Status Surat Pengantar</td><td>{{ $pengajuan->status_surat_pengantar ?? 'belum_ada' }}</td></tr>
            <tr><td>Status Surat Keterangan</td><td>{{ $pengajuan->status_surat_keterangan ?? 'belum_ada' }}</td></tr>
            @if($pengajuan->nomor_surat_balasan || $pengajuan->tanggal_surat_balasan)
                <tr><td>Surat Balasan Instansi</td><td>{{ $pengajuan->nomor_surat_balasan ?? '-' }} / {{ $pengajuan->tanggal_surat_balasan ?? '-' }}</td></tr>
            @endif
        @endif
    </table>
</div>

@if($isSuratPengantar)
    @if($pengajuan->status_pengajuan === 'dibatalkan')
        <div class="alert alert-secondary">Pengajuan ini telah dibatalkan oleh mahasiswa.</div>
    @endif

    @if($pengajuan->status_pengajuan === 'ditolak')
        <div class="alert alert-danger">
            <div class="fw-semibold">Pengajuan ditolak oleh admin.</div>
            @if($pengajuan->catatan_admin)
                <div class="small mt-1">Alasan: {{ $pengajuan->catatan_admin }}</div>
            @endif
        </div>
    @endif

    @if($pengajuan->status_pengajuan === 'revisi')
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Pengajuan perlu direvisi</div>
                <div class="small">Perbaiki data sesuai catatan admin lalu kirim ulang untuk diverifikasi.</div>
                @if($pengajuan->catatan_admin)
                    <div class="small mt-1">Catatan: {{ $pengajuan->catatan_admin }}</div>
                @endif
            </div>
            <a href="{{ route('mahasiswa.pengajuan.edit', $pengajuan->id) }}" class="btn btn-warning">Edit/Revisi Pengajuan</a>
        </div>
    @endif

    <div class="card p-4">
        <h6 class="fw-bold mb-3">Dokumen Surat Pengantar/Rekomendasi</h6>
        @if($pengajuan->status_pengajuan === 'dibatalkan')
            <p class="text-muted mb-0">Pengajuan ini telah dibatalkan oleh mahasiswa.</p>
        @elseif($pengajuan->status_pengajuan === 'ditolak')
            <p class="text-muted mb-0">Tidak ada dokumen surat karena pengajuan ditolak.</p>
        @elseif($hasFile($suratPengantar))
            <p class="text-muted mb-3">Surat Pengantar/Rekomendasi Magang sudah diterbitkan oleh admin.</p>
            <a href="{{ route('mahasiswa.dokumen.preview', $suratPengantar->id) }}" target="_blank" class="btn btn-outline-primary">Preview</a>
            <a href="{{ route('mahasiswa.dokumen.download', $suratPengantar->id) }}" class="btn btn-success">Download</a>
        @elseif($pengajuan->status_pengajuan === 'disetujui')
            <p class="text-muted mb-0">Pengajuan sudah disetujui. Surat Pengantar/Rekomendasi sedang diproses admin.</p>
        @else
            <p class="text-muted mb-0">Surat belum tersedia. Silakan tunggu admin menyetujui dan mengupload surat.</p>
        @endif
    </div>
@elseif($isSkMagang)
    <div class="card p-4 mt-4">
        <h6 class="fw-bold mb-3">Dokumen Hasil Pengajuan SK Magang</h6>
        <div class="mb-3">
            <div class="fw-semibold">Surat Keterangan Magang</div>
            @if($hasFile($suratKeterangan))
                <a href="{{ route('mahasiswa.dokumen.preview', $suratKeterangan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Preview</a>
                <a href="{{ route('mahasiswa.dokumen.download', $suratKeterangan->id) }}" class="btn btn-sm btn-success mt-2">Download</a>
            @else
                <p class="text-muted mb-0">Dokumen belum tersedia.</p>
            @endif
        </div>
        <div>
            <div class="fw-semibold">SK Magang</div>
            @if($hasFile($skMagang))
                <a href="{{ route('mahasiswa.dokumen.preview', $skMagang->id) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Preview</a>
                <a href="{{ route('mahasiswa.dokumen.download', $skMagang->id) }}" class="btn btn-sm btn-success mt-2">Download</a>
            @else
                <p class="text-muted mb-0">Dokumen belum tersedia.</p>
            @endif
        </div>
    </div>
@else
    <div class="card p-4 mt-4">
        <h6 class="fw-bold mb-3">Seminar Magang</h6>
        <p class="mb-2">Status seminar: <span class="badge bg-secondary">{{ $pengajuan->status_seminar ?? 'belum' }}</span></p>
        <a href="{{ route('mahasiswa.seminar.index') }}" class="btn btn-primary">Buka Menu Seminar Magang</a>
    </div>

    @if($pengajuan->bimbingans->count())
    <div class="card p-4 mb-4">
        <h6 class="fw-bold mb-3">Dosen Pembimbing</h6>
        @foreach($pengajuan->bimbingans as $b)
        <p class="mb-0">{{ $b->dosen->nama_dosen ?? '-' }}</p>
        @endforeach
    </div>
    @endif

    <div class="card p-4">
        <h6 class="fw-bold mb-3">Riwayat Status</h6>
        @forelse($pengajuan->statusHistories as $s)
        <div class="border-bottom pb-2 mb-2">
            <span class="badge bg-secondary">{{ $s->status }}</span>
            <small class="text-muted ms-2">{{ $s->created_at->format('d M Y') }}</small>
            @if($s->keterangan)<p class="mb-0 mt-1 small">{{ $s->keterangan }}</p>@endif
        </div>
        @empty
        <p class="text-muted">Belum ada riwayat</p>
        @endforelse
    </div>
@endif
@endsection
