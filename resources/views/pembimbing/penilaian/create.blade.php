@extends('layouts.app')
@section('title', 'Penilaian Lapangan')
@section('page-title', 'Input Nilai Pembimbing Lapangan')

@section('content')
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Penilaian belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Mahasiswa: {{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</h6>
            <div class="text-muted small">NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }} / Program Studi: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
        </div>
        <a href="{{ route('pembimbing.penilaian.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @if(config('mbkm.absensi_aktif') && isset($rekapAbsensi))
    <div class="alert alert-info mb-4">
        <div class="fw-semibold mb-2">Rekap Absensi sebagai bahan nilai</div>
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
    <form action="{{ route('pembimbing.penilaian.store', $pengajuan->id) }}" method="POST">
        @csrf
        <div class="alert alert-info">Nilai pembimbing lapangan dihitung dari 5 komponen berbobot 100%. Nilai akhir mahasiswa = 50% nilai dosen pembimbing + 50% nilai pembimbing lapangan.</div>
        <div class="row g-3">
            @php
                $fields = [
                    'pembimbing_kehadiran_disiplin' => ['Kehadiran dan Disiplin', '15%'],
                    'pembimbing_kinerja_sikap' => ['Kinerja dan Sikap Kerja', '30%'],
                    'pembimbing_logbook_kegiatan' => ['Logbook/Kegiatan Harian Magang', '15%'],
                    'pembimbing_luaran' => ['Luaran/Hasil Pekerjaan Magang', '20%'],
                    'pembimbing_laporan_akhir' => ['Laporan Akhir Magang', '20%'],
                ];
            @endphp
            @foreach($fields as $name => [$label, $bobot])
            <div class="col-md-6">
                <label class="form-label fw-semibold">{{ $label }} <span class="text-muted small">bobot {{ $bobot }}</span></label>
                <input type="number" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" min="0" max="100" value="{{ old($name, $penilaian?->{$name}) }}" required>
                @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endforeach
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Pembimbing Lapangan</label>
                <textarea name="catatan_mitra" class="form-control @error('catatan_mitra') is-invalid @enderror" rows="3">{{ old('catatan_mitra', $penilaian->catatan_mitra ?? '') }}</textarea>
                @error('catatan_mitra')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @if($penilaian)
        <div class="alert alert-info mt-4 mb-0">
            Nilai pembimbing lapangan saat ini: <strong>{{ $penilaian->nilai_pembimbing_total !== null ? number_format($penilaian->nilai_pembimbing_total, 2) : 'Belum tersedia' }}</strong>
            @if($penilaian->nilai_akhir !== null)
                / Nilai Akhir: <strong>{{ number_format($penilaian->nilai_akhir, 2) }}</strong> / Grade: <strong>{{ $penilaian->grade }}</strong>
            @else
                / Nilai akhir belum tersedia karena nilai dosen pembimbing dan pembimbing lapangan belum lengkap.
            @endif
        </div>
        @endif

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">Simpan Nilai</button>
            <a href="{{ route('pembimbing.penilaian.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @endunless
</div>
@endsection