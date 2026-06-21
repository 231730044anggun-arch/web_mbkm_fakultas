@extends('layouts.app')
@section('title', 'Revisi Pengajuan')
@section('page-title', 'Revisi Pengajuan Surat Pengantar')

@section('content')
<div class="card p-4">
    <h6 class="student-card-title mb-3">Revisi Pengajuan Surat Pengantar/Rekomendasi Magang</h6>

    @if($pengajuan->catatan_admin)
        <div class="alert alert-warning">
            <div class="fw-semibold mb-1">Catatan Revisi Admin</div>
            {{ $pengajuan->catatan_admin }}
        </div>
    @endif

    <form action="{{ route('mahasiswa.pengajuan.update', $pengajuan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Periode Magang <span class="text-danger">*</span></label>
            <select name="periode_id" class="form-select" required>
                <option value="">-- Pilih Periode --</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" @selected(old('periode_id', $pengajuan->periode_id) == $p->id)>{{ $p->nama_periode }} ({{ $p->tahun }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Mitra <span class="text-danger">*</span></label>
            <select name="jenis_mitra" id="jenisMitra" class="form-select" required>
                <option value="">-- Pilih Jenis Mitra --</option>
                <option value="ber_mou" @selected(old('jenis_mitra', $pengajuan->jenis_mitra) === 'ber_mou')>Mitra Ber-MoU</option>
                <option value="non_mou" @selected(old('jenis_mitra', $pengajuan->jenis_mitra) === 'non_mou')>Mitra Non-MoU</option>
                <option value="baru" @selected(old('jenis_mitra', $pengajuan->jenis_mitra) === 'baru')>Tambah Mitra Baru</option>
            </select>
        </div>

        <div id="formMitraBerMou" class="d-none" data-form-section>
            <label class="form-label fw-semibold">Pilih Mitra Ber-MoU</label>
            <select name="mitra_id" class="form-select">
                <option value="">-- Pilih Mitra Ber-MoU --</option>
                @foreach($mitrasBerMou as $m)
                    <option value="{{ $m->id }}" @selected(old('mitra_id', $pengajuan->mitra_id) == $m->id)>{{ $m->nama_instansi }} ({{ $m->kota }})</option>
                @endforeach
            </select>
        </div>

        <div id="formMitraNonMou" class="d-none" data-form-section>
            <label class="form-label fw-semibold">Pilih Mitra Non-MoU</label>
            <select name="mitra_id" class="form-select">
                <option value="">-- Pilih Mitra Non-MoU --</option>
                @foreach($mitrasNonMou as $m)
                    <option value="{{ $m->id }}" @selected(old('mitra_id', $pengajuan->mitra_id) == $m->id)>{{ $m->nama_instansi }} ({{ $m->kota }})</option>
                @endforeach
            </select>
        </div>

        <div id="formMitraBaru" class="d-none" data-form-section>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Instansi</label>
                    <input type="text" name="nama_instansi_manual" class="form-control" value="{{ old('nama_instansi_manual', $pengajuan->nama_instansi_manual) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kota Instansi</label>
                    <input type="text" name="kota_instansi_manual" class="form-control" value="{{ old('kota_instansi_manual', $pengajuan->kota_instansi_manual) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat Instansi</label>
                    <textarea name="alamat_instansi_manual" class="form-control" rows="2">{{ old('alamat_instansi_manual', $pengajuan->alamat_instansi_manual) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Bidang Instansi</label>
                    <input type="text" name="bidang_industri" class="form-control" value="{{ old('bidang_industri', $pengajuan->mitra->bidang_industri ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email Instansi</label>
                    <input type="email" name="email_instansi" class="form-control" value="{{ old('email_instansi', $pengajuan->mitra->email ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nomor Telepon Instansi</label>
                    <input type="text" name="no_telp_instansi" class="form-control" value="{{ old('no_telp_instansi', $pengajuan->mitra->no_telp ?? '') }}">
                </div>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Posisi/Bidang Magang <span class="text-danger">*</span></label>
                <input type="text" name="posisi_magang" class="form-control" value="{{ old('posisi_magang', $pengajuan->posisi_magang) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $pengajuan->tanggal_mulai) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $pengajuan->tanggal_selesai) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Rencana/Deskripsi Kegiatan</label>
                <textarea name="deskripsi_kegiatan" class="form-control" rows="3">{{ old('deskripsi_kegiatan', $pengajuan->deskripsi_kegiatan) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-4 mt-3">Kirim Ulang Revisi</button>
        <a href="{{ route('mahasiswa.pengajuan.show', $pengajuan->id) }}" class="btn btn-secondary px-4 mt-3">Batal</a>
    </form>
</div>

@push('scripts')
<script>
function setSection(id, visible) {
    const section = document.getElementById(id);
    if (!section) return;
    section.classList.toggle('d-none', !visible);
    section.querySelectorAll('input, select, textarea').forEach((input) => input.disabled = !visible);
}
function refreshMitraChoice() {
    const select = document.getElementById('jenisMitra');
    if (!select) return;
    setSection('formMitraBerMou', select.value === 'ber_mou');
    setSection('formMitraNonMou', select.value === 'non_mou');
    setSection('formMitraBaru', select.value === 'baru');
}
document.getElementById('jenisMitra')?.addEventListener('change', refreshMitraChoice);
refreshMitraChoice();
</script>
@endpush
@endsection
