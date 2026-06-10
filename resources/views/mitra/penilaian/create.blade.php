@extends('layouts.app')
@section('title', 'Penilaian Mahasiswa')
@section('page-title', 'Input Penilaian Lapangan')

@section('content')
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Penilaian belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Mahasiswa: {{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
            <div class="text-muted small">NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }} / Program Studi: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
        </div>
        <a href="{{ route('mitra.penilaian.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @if(isset($rekapAbsensi))
    <div class="alert alert-info mb-4">
        <div class="fw-semibold mb-2">Rekap Absensi sebagai bahan nilai Absensi/Kehadiran</div>
        <div class="row g-2 small">
            <div class="col-md-2">Hari wajib: <strong>{{ $rekapAbsensi['total_hari_wajib'] }}</strong></div>
            <div class="col-md-2">Masuk: <strong>{{ $rekapAbsensi['jumlah_absensi_masuk'] }}</strong></div>
            <div class="col-md-2">Disetujui: <strong>{{ $rekapAbsensi['jumlah_disetujui'] }}</strong></div>
            <div class="col-md-2">Pending: <strong>{{ $rekapAbsensi['jumlah_pending'] }}</strong></div>
            <div class="col-md-2">Revisi/Tolak: <strong>{{ $rekapAbsensi['jumlah_revisi'] }}</strong></div>
            <div class="col-md-2">Kehadiran valid: <strong>{{ $rekapAbsensi['persentase_kehadiran'] }}%</strong></div>
        </div>
    </div>
    @endif

    @unless($canInput)
        <div class="alert alert-warning mb-0">Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.</div>
    @else
    <form action="{{ route('mitra.penilaian.store', $pengajuan->id) }}" method="POST">
        @csrf
        <div class="alert alert-info">Nilai Lapangan = (Absensi x 10%) + (Sikap x 15%) + (Teamwork x 15%) + (Kedisiplinan x 20%). Total kontribusi maksimal 60 poin terhadap Nilai Akhir.</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Absensi/Kehadiran <span class="text-muted small">bobot 10%</span></label>
                <input type="number" name="nilai_absensi" class="form-control @error('nilai_absensi') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_absensi', $penilaian->nilai_absensi ?? '') }}" required>
                @error('nilai_absensi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Sikap dan Etika Kerja <span class="text-muted small">bobot 15%</span></label>
                <input type="number" name="nilai_sikap_etika" class="form-control @error('nilai_sikap_etika') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_sikap_etika', $penilaian->nilai_sikap_etika ?? '') }}" required>
                @error('nilai_sikap_etika')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Teamwork/Kerja Sama Tim <span class="text-muted small">bobot 15%</span></label>
                <input type="number" name="nilai_teamwork" class="form-control @error('nilai_teamwork') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_teamwork', $penilaian->nilai_teamwork ?? '') }}" required>
                @error('nilai_teamwork')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kedisiplinan dan Tanggung Jawab <span class="text-muted small">bobot 20%</span></label>
                <input type="number" name="nilai_disiplin_tanggung_jawab" class="form-control @error('nilai_disiplin_tanggung_jawab') is-invalid @enderror" min="0" max="100" value="{{ old('nilai_disiplin_tanggung_jawab', $penilaian->nilai_disiplin_tanggung_jawab ?? '') }}" required>
                @error('nilai_disiplin_tanggung_jawab')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Mitra</label>
                <textarea name="catatan_mitra" class="form-control @error('catatan_mitra') is-invalid @enderror" rows="3">{{ old('catatan_mitra', $penilaian->catatan_mitra ?? $penilaian->catatan ?? '') }}</textarea>
                @error('catatan_mitra')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @if($penilaian && $penilaian->nilai_lapangan !== null)
        <div class="alert alert-info mt-4 mb-0">
            Nilai Lapangan saat ini: <strong>{{ number_format($penilaian->nilai_lapangan, 2) }}</strong>
            @if($penilaian->nilai_akhir !== null)
                / Nilai Akhir: <strong>{{ number_format($penilaian->nilai_akhir, 2) }}</strong> / Grade: <strong>{{ $penilaian->grade }}</strong>
            @else
                / Nilai Akhir belum tersedia karena Nilai Lapangan dan Nilai Akademik belum lengkap.
            @endif
        </div>
        @endif

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">Simpan Nilai Lapangan</button>
            <a href="{{ route('mitra.penilaian.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @endunless
</div>
@endsection
