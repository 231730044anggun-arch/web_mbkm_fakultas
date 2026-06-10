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
    $pengajuanAwal = $pengajuan->pengajuanAwal;
    $dokumenVerifikasi = $isSkMagang
        ? $pengajuan->dokumens->whereIn('jenis_dokumen', ['surat_diterima', 'proposal_magang'])
        : $pengajuan->dokumens;
@endphp
<div class="row g-4">
    <div class="{{ $isSuratPengantar ? 'col-md-12' : 'col-md-8' }}">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">Informasi Pengajuan</h6>
            <table class="table table-borderless">
                <tr><td width="200">Mahasiswa</td><td>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
                <tr><td>NIM</td><td>{{ $pengajuan->mahasiswa->nim ?? '-' }}</td></tr>
                <tr><td>Instansi</td><td>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td></tr>
                <tr><td>Alamat Instansi</td><td>{{ $pengajuan->mitra->alamat ?? $pengajuan->alamat_instansi_manual ?? '-' }}</td></tr>
                <tr><td>Kota Instansi</td><td>{{ $pengajuan->mitra->kota ?? $pengajuan->kota_instansi_manual ?? '-' }}</td></tr>
                <tr><td>Bidang Instansi</td><td>{{ $pengajuan->mitra->bidang_industri ?? '-' }}</td></tr>
                <tr><td>Posisi</td><td>{{ $pengajuan->posisi_magang }}</td></tr>
                <tr><td>Periode</td><td>{{ $pengajuan->periode->nama_periode ?? '-' }}</td></tr>
                <tr><td>Jenis Pengajuan</td><td>{{ $isSkMagang ? 'SK Magang' : 'Surat Pengantar/Rekomendasi Magang' }}</td></tr>
                <tr><td>Tanggal</td><td>{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</td></tr>
                <tr><td>Rencana/Deskripsi Kegiatan</td><td>{{ $pengajuan->deskripsi_kegiatan ?? '-' }}</td></tr>
                @if($isSkMagang)
                    <tr><td>Pengajuan Surat Pengantar Terkait</td><td>
                        @if($pengajuanAwal)
                            <a href="{{ route('admin.pengajuan.show', $pengajuanAwal->id) }}">#{{ $pengajuanAwal->id }} - {{ $pengajuanAwal->mitra->nama_instansi ?? $pengajuanAwal->nama_instansi_manual ?? '-' }}</a>
                        @else
                            -
                        @endif
                    </td></tr>
                    <tr><td>Nomor Surat Balasan</td><td>{{ $pengajuan->nomor_surat_balasan ?? '-' }}</td></tr>
                    <tr><td>Tanggal Surat Balasan</td><td>{{ $pengajuan->tanggal_surat_balasan ?? '-' }}</td></tr>
                    <tr><td>Pembimbing Lapangan</td><td>{{ $pengajuan->mitra->pembimbing_lapangan_nama ?? '-' }}</td></tr>
                    <tr><td>Jabatan Pembimbing</td><td>{{ $pengajuan->mitra->pembimbing_lapangan_jabatan ?? '-' }}</td></tr>
                    <tr><td>Kontak Pembimbing</td><td>{{ $pengajuan->mitra->pembimbing_lapangan_kontak ?? '-' }}</td></tr>
                @endif
                <tr><td>Status</td><td><span class="badge bg-warning">{{ $pengajuan->status_pengajuan }}</span></td></tr>
                @if($pengajuan->catatan_admin)
                <tr><td>Catatan Admin</td><td>{{ $pengajuan->catatan_admin }}</td></tr>
                @endif
            </table>
        </div>

        @if($pengajuan->mitra)
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">Status Mitra</h6>
            <table class="table table-borderless">
                <tr><td width="200">Nama Mitra</td><td>{{ $pengajuan->mitra->nama_instansi }}</td></tr>
                <tr><td>Status Mitra</td><td>{{ ucfirst(str_replace('_', ' ', $pengajuan->mitra->status_mitra_detail ?? 'tidak tersedia')) }}</td></tr>
                <tr><td>Status MoU</td><td>{{ ucfirst(str_replace('_', ' ', $pengajuan->mitra->status_mou ?? 'tidak')) }}</td></tr>
                @if($pengajuan->mitra->status_mitra_detail === 'menunggu_verifikasi')
                <tr><td>Catatan</td><td class="text-warning">
                    @if($isSuratPengantar)
                        Calon mitra baru belum aktif. Pengajuan surat pengantar tetap bisa diproses tanpa mengaktifkan mitra.
                    @else
                        Mitra baru menunggu verifikasi dari bukti diterima/SK Magang.
                    @endif
                </td></tr>
                @endif
            </table>
        </div>
        @endif

        @unless($isSuratPengantar)
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">Status Dokumen</h6>
            <table class="table table-borderless">
                <tr><td width="200">Surat Pengantar</td><td><span class="badge bg-{{ $pengajuan->status_surat_pengantar === 'valid' ? 'success' : ($pengajuan->status_surat_pengantar === 'revisi' ? 'danger' : 'warning') }}">{{ $pengajuan->status_surat_pengantar }}</span></td></tr>
                @unless($isSuratPengantar)
                <tr><td>Surat Keterangan</td><td><span class="badge bg-{{ $pengajuan->status_surat_keterangan === 'valid' ? 'success' : ($pengajuan->status_surat_keterangan === 'revisi' ? 'danger' : 'warning') }}">{{ $pengajuan->status_surat_keterangan ?? 'belum_ada' }}</span></td></tr>
                @unless($isSkMagang)
                <tr><td>Laporan</td><td><span class="badge bg-{{ $pengajuan->status_laporan === 'valid' ? 'success' : ($pengajuan->status_laporan === 'revisi' ? 'danger' : 'warning') }}">{{ $pengajuan->status_laporan }}</span></td></tr>
                @endunless
                @endunless
            </table>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Jenis</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($dokumenVerifikasi as $dokumen)
                        <tr>
                            <td>{{ str_replace('_', ' ', $dokumen->jenis_dokumen) }}</td>
                            <td>{{ $dokumen->tanggal_upload }}</td>
                            <td>
                                @php $badge = ['pending'=>'warning','valid'=>'success','revisi'=>'danger']; @endphp
                                <span class="badge bg-{{ $badge[$dokumen->status_verifikasi] ?? 'secondary' }}">{{ $dokumen->status_verifikasi }}</span>
                            </td>
                            <td>
                                @if($hasFile($dokumen))
                                    <a href="{{ route('admin.dokumen.preview', $dokumen->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                    <a href="{{ route('admin.dokumen.download', $dokumen->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada dokumen</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($dokumenVerifikasi->count())
            <form action="{{ route('admin.pengajuan.dokumen.status', $pengajuan->id) }}" method="POST" class="mt-4">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pilih Dokumen</label>
                        <select name="dokumen_id" class="form-select" required>
                            <option value="">-- Pilih Dokumen --</option>
                            @foreach($dokumenVerifikasi as $dokumen)
                                <option value="{{ $dokumen->id }}">{{ str_replace('_', ' ', $dokumen->jenis_dokumen) }} - {{ $dokumen->tanggal_upload }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status Verifikasi</label>
                        <select name="status_verifikasi" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="valid">Valid</option>
                            <option value="revisi">Revisi</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan Verifikasi</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Perbarui Status Dokumen</button>
                    </div>
                </div>
            </form>
            @endif
        </div>
        @endunless

        @if($isSuratPengantar && $pengajuan->status_pengajuan === 'selesai')
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Dokumen Surat Pengantar/Rekomendasi</h6>
            @if($hasFile($suratPengantar))
                <p class="text-muted mb-3">Pengajuan sudah selesai dan surat sudah diupload admin.</p>
                <a href="{{ route('admin.dokumen.preview', $suratPengantar->id) }}" target="_blank" class="btn btn-outline-primary">Preview</a>
                <a href="{{ route('admin.dokumen.download', $suratPengantar->id) }}" class="btn btn-success">Download</a>
            @else
                <p class="text-muted mb-0">Status selesai, tetapi file surat belum ditemukan.</p>
            @endif
        </div>
        @elseif($pengajuan->status_pengajuan === 'dibatalkan')
        <div class="alert alert-secondary">Pengajuan ini telah dibatalkan oleh mahasiswa. Tidak ada proses lanjutan yang perlu dilakukan.</div>
        @else
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Update Status</h6>
            <form action="{{ route('admin.pengajuan.status', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ $pengajuan->status_pengajuan === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ $pengajuan->status_pengajuan === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="revisi" {{ $pengajuan->status_pengajuan === 'revisi' ? 'selected' : '' }}>Revisi</option>
                        <option value="ditolak" {{ $pengajuan->status_pengajuan === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        @unless($isSuratPengantar)
                        <option value="berjalan" {{ $pengajuan->status_pengajuan === 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                        @endunless
                        <option value="selesai" {{ $pengajuan->status_pengajuan === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                @if(!$isSuratPengantar && $pengajuan->mitra && $pengajuan->mitra->status_mitra_detail === 'menunggu_verifikasi')
                <div class="mb-3">
                    <label class="form-label">Verifikasi Mitra</label>
                    <select name="verify_mitra_status" class="form-select">
                        <option value="menunggu_verifikasi" selected>Menunggu Verifikasi</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                @endif                @if($isSuratPengantar)
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
                </div>
                @endif
                @if($isSuratPengantar)
                <div class="mb-3">
                    <label class="form-label">Upload Surat Pengantar/Rekomendasi Magang</label>
                    <input type="file" name="surat_pengantar_file" class="form-control" accept=".pdf,.doc,.docx">
                    @if($hasFile($suratPengantar))
                        <div class="small text-muted mt-2">
                            File surat sudah tersedia. Upload file baru hanya jika ingin mengganti.
                            <a href="{{ route('admin.dokumen.preview', $suratPengantar->id) }}" target="_blank">Preview</a>
                            <a href="{{ route('admin.dokumen.download', $suratPengantar->id) }}">Download</a>
                        </div>
                    @else
                        <div class="small text-muted mt-2">Wajib diupload saat status diubah menjadi Selesai. Status Disetujui boleh disimpan tanpa file.</div>
                    @endif
                </div>
                @endif
                @if($isSkMagang)
                <div class="mb-3">
                    <label class="form-label">Upload Surat Keterangan Magang</label>
                    <input type="file" name="surat_keterangan_file" class="form-control" accept=".pdf,.doc,.docx">
                    <div class="small text-muted mt-2">
                        @if($hasFile($suratKeterangan))
                            <div class="mb-1">Surat Keterangan Magang tersedia.</div>
                            <a href="{{ route('admin.dokumen.preview', $suratKeterangan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                            <a href="{{ route('admin.dokumen.download', $suratKeterangan->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                        @else
                            Surat Keterangan Magang belum tersedia.
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload SK Magang</label>
                    <input type="file" name="sk_magang_file" class="form-control" accept=".pdf,.doc,.docx">
                    <div class="small text-muted mt-2">
                        @if($hasFile($skMagang))
                            <div class="mb-1">SK Magang tersedia.</div>
                            <a href="{{ route('admin.dokumen.preview', $skMagang->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                            <a href="{{ route('admin.dokumen.download', $skMagang->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                        @else
                            SK Magang belum tersedia.
                        @endif
                    </div>
                </div>
                @endif
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
        @endif
    </div>

    @unless($isSuratPengantar)
    <div class="col-md-4">
        @if($isSkMagang)
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Tugaskan Dosen Pembimbing</h6>
            <form action="{{ route('admin.pengajuan.dosen', $pengajuan->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Pilih Dosen</label>
                    <select name="dosen_id" class="form-select">
                        <option value="">-- Pilih --</option>
                        @foreach($dosens as $d)
                            <option value="{{ $d->id }}" @selected($pengajuan->bimbingans->first()?->dosen_id === $d->id)>{{ $d->nama_dosen }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">Tugaskan</button>
            </form>
        </div>
        @endif

        @unless($isSkMagang)
        <div class="card p-4 mt-4">
            <h6 class="fw-bold mb-3">Penjadwalan Seminar</h6>
            <form action="{{ route('admin.pengajuan.seminar', $pengajuan->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Judul Laporan</label>
                    <input type="text" name="judul_laporan" class="form-control" value="{{ $pengajuan->judul_laporan }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Seminar</label>
                    <input type="date" name="seminar_tanggal" class="form-control" value="{{ $pengajuan->seminar_tanggal }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jam Seminar</label>
                    <input type="text" name="seminar_jam" class="form-control" value="{{ $pengajuan->seminar_jam }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <input type="text" name="seminar_ruangan" class="form-control" value="{{ $pengajuan->seminar_ruangan }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Seminar</label>
                    <select name="status_seminar" class="form-select" required>
                        <option value="belum" {{ $pengajuan->status_seminar === 'belum' ? 'selected' : '' }}>Belum</option>
                        <option value="terjadwal" {{ $pengajuan->status_seminar === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                        <option value="selesai" {{ $pengajuan->status_seminar === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Jadwal Seminar</button>
            </form>
            <div class="mt-3">
                <a href="{{ route('admin.pengajuan.surat.keterangan', $pengajuan->id) }}" class="btn btn-outline-secondary w-100 mb-2">Download Surat Keterangan Magang</a>
                <a href="{{ route('admin.pengajuan.surat.seminar', $pengajuan->id) }}" class="btn btn-outline-secondary w-100 mb-2">Download Surat Seminar</a>
                <form action="{{ route('admin.surat.sk.kolektif') }}" method="POST">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $pengajuan->periode_id }}">
                    <button type="submit" class="btn btn-outline-primary w-100">Generate / Download SK Magang Kolektif (Periode)</button>
                </form>
            </div>
        </div>
        @endunless
    </div>
    @endunless
</div>
@endsection
