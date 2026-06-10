@extends('layouts.app')
@section('title', 'Pengajuan')
@section('page-title', 'Form Pengajuan')

@section('content')
@if(!empty($eligibility) && !$eligibility['allowed'])
<div class="card p-4">
    <div class="alert alert-danger mb-0">
        <h6 class="mb-2">Syarat Pengajuan Tidak Terpenuhi</h6>
        <ul class="mb-0">
            @foreach($eligibility['reasons'] as $reason)
                <li>{{ $reason }}</li>
            @endforeach
        </ul>
    </div>
    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-secondary mt-3">Kembali ke Pengajuan</a>
</div>
@elseif(!$jenisPengajuan)
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-2">Surat Pengantar/Rekomendasi Magang</h6>
            <p class="text-muted small">Untuk meminta surat dari fakultas sebelum dikirim ke instansi.</p>
            <a href="{{ route('mahasiswa.pengajuan.create', ['jenis' => 'surat_pengantar']) }}" class="btn btn-primary mt-auto">Pilih</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-2">SK Magang</h6>
            <p class="text-muted small">Untuk mengupload bukti diterima instansi, proposal, dan meminta dokumen SK Magang.</p>
            <a href="{{ route('mahasiswa.pengajuan.create', ['jenis' => 'surat_keterangan']) }}" class="btn btn-primary mt-auto">Pilih</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-2">Seminar Magang</h6>
            <p class="text-muted small">Untuk mengajukan seminar setelah syarat laporan dan logbook terpenuhi.</p>
            <a href="{{ route('mahasiswa.seminar.index') }}" class="btn btn-primary mt-auto">Buka Seminar</a>
        </div>
    </div>
</div>
@elseif($jenisPengajuan === 'surat_keterangan')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Pengajuan SK Magang</h6>
    @if($pengajuanDisetujui->isEmpty())
        <div class="alert alert-warning mb-0">Belum ada Pengajuan Surat Pengantar/Rekomendasi Magang yang disetujui dan sudah terbit.</div>
    @else
    <form action="{{ route('mahasiswa.pengajuan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="jenis_pengajuan" value="surat_keterangan">
        <div class="mb-3">
            <label class="form-label fw-semibold">Pengajuan Surat Pengantar yang Disetujui</label>
            <select name="pengajuan_awal_id" class="form-select" required>
                <option value="">-- Pilih Pengajuan --</option>
                @foreach($pengajuanDisetujui as $p)
                    <option value="{{ $p->id }}" @selected(old('pengajuan_awal_id') == $p->id)>
                        {{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual ?? '-' }} - {{ $p->periode->nama_periode ?? '-' }} - {{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Upload Surat Balasan/Bukti Diterima <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Upload Proposal Magang <span class="text-danger">*</span></label>
                <input type="file" name="proposal_magang" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Nomor Surat Balasan</label>
                <input type="text" name="nomor_surat_balasan" class="form-control" value="{{ old('nomor_surat_balasan') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Surat Balasan</label>
                <input type="date" name="tanggal_surat_balasan" class="form-control" value="{{ old('tanggal_surat_balasan') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Nama Pembimbing Lapangan</label>
                <input type="text" name="nama_pembimbing_lapangan" class="form-control" value="{{ old('nama_pembimbing_lapangan') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Jabatan Pembimbing Lapangan</label>
                <input type="text" name="jabatan_pembimbing_lapangan" class="form-control" value="{{ old('jabatan_pembimbing_lapangan') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Kontak Pembimbing Lapangan</label>
                <input type="text" name="kontak_pembimbing_lapangan" class="form-control" value="{{ old('kontak_pembimbing_lapangan') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Tambahan</label>
                <textarea name="catatan_mahasiswa" class="form-control" rows="2">{{ old('catatan_mahasiswa') }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary px-4 mt-3">Kirim Pengajuan</button>
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-secondary px-4 mt-3">Kembali</a>
    </form>
    @endif
</div>
@else
<div class="card p-4">

    <form action="{{ route('mahasiswa.pengajuan.store') }}" method="POST">
        @csrf
        <input type="hidden" name="jenis_pengajuan" value="surat_pengantar">
        <div class="mb-3">
            <label class="form-label fw-semibold">Periode Magang <span class="text-danger">*</span></label>
            <select name="periode_id" class="form-select" required {{ !empty($eligibility) && !$eligibility['allowed'] ? 'disabled' : '' }}>
                <option value="">-- Pilih Periode --</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" @selected(old('periode_id') == $p->id)>{{ $p->nama_periode }} ({{ $p->tahun }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Mitra <span class="text-danger">*</span></label>
            <select name="jenis_mitra" id="jenisMitra" class="form-select" required {{ !empty($eligibility) && !$eligibility['allowed'] ? 'disabled' : '' }}>
                <option value="">-- Pilih Jenis Mitra --</option>
                <option value="ber_mou" @selected(old('jenis_mitra') === 'ber_mou')>Mitra Ber-MoU</option>
                <option value="non_mou" @selected(old('jenis_mitra') === 'non_mou')>Mitra Non-MoU</option>
                <option value="baru" @selected(old('jenis_mitra') === 'baru')>Tambah Mitra Baru</option>
            </select>
        </div>
        <input type="hidden" name="jenis_magang" value="mitra">
        <div id="formMitraBerMou" class="d-none" data-form-section>
            <label class="form-label fw-semibold">Pilih Mitra Ber-MoU</label>
            <select name="mitra_id" class="form-select">
                <option value="">-- Pilih Mitra Ber-MoU --</option>
                @foreach($mitrasBerMou as $m)
                    <option value="{{ $m->id }}" @selected(old('mitra_id') == $m->id)>{{ $m->nama_instansi }} ({{ $m->kota }})</option>
                @endforeach
            </select>
        </div>
        <div id="formMitraNonMou" class="d-none" data-form-section>
            <label class="form-label fw-semibold">Pilih Mitra Non-MoU</label>
            <select name="mitra_id" class="form-select">
                <option value="">-- Pilih Mitra Non-MoU --</option>
                @foreach($mitrasNonMou as $m)
                    <option value="{{ $m->id }}" @selected(old('mitra_id') == $m->id)>{{ $m->nama_instansi }} ({{ $m->kota }})</option>
                @endforeach
            </select>
        </div>
        <div id="formMitraBaru" class="d-none" data-form-section>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Nama Instansi</label><input type="text" name="nama_instansi_manual" class="form-control" value="{{ old('nama_instansi_manual') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Kota Instansi</label><input type="text" name="kota_instansi_manual" class="form-control" value="{{ old('kota_instansi_manual') }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Alamat Instansi</label><textarea name="alamat_instansi_manual" class="form-control" rows="2">{{ old('alamat_instansi_manual') }}</textarea></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Bidang Instansi</label><input type="text" name="bidang_industri" class="form-control" value="{{ old('bidang_industri') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Email Instansi</label><input type="email" name="email_instansi" class="form-control" value="{{ old('email_instansi') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Nomor Telepon Instansi</label><input type="text" name="no_telp_instansi" class="form-control" value="{{ old('no_telp_instansi') }}"></div>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6"><label class="form-label fw-semibold">Posisi/Bidang Magang <span class="text-danger">*</span></label><input type="text" name="posisi_magang" class="form-control" value="{{ old('posisi_magang') }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label><input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label><input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}" required></div>
            <div class="col-12"><label class="form-label fw-semibold">Rencana/Deskripsi Kegiatan</label><textarea name="deskripsi_kegiatan" class="form-control" rows="3">{{ old('deskripsi_kegiatan') }}</textarea></div>
        </div>
        <button type="submit" class="btn btn-primary px-4 mt-3" {{ !empty($eligibility) && !$eligibility['allowed'] ? 'disabled' : '' }}>Kirim Pengajuan</button>
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-secondary px-4 mt-3">Kembali</a>
    </form>
</div>
@endif

@push('scripts')
<script>
const sectionIds = ['formMitraBerMou', 'formMitraNonMou', 'formMitraBaru'];
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
sectionIds.forEach((id) => setSection(id, false));
document.getElementById('jenisMitra')?.addEventListener('change', refreshMitraChoice);
refreshMitraChoice();
</script>
@endpush
@endsection
