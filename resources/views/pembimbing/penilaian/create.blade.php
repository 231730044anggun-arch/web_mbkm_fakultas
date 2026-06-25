@extends('layouts.app')
@section('title', 'Penilaian Lapangan')
@section('page-title', 'Input Nilai Pembimbing Lapangan')

@section('content')
<style>
    .assessment-page { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
    .assessment-section { border: 1px solid #e7ddff; border-radius: 14px; padding: 18px; background: #fff; }
    .assessment-section-title { font-size: 18px; font-weight: 700; color: #2f235f; margin-bottom: 4px; }
    .assessment-info { background: #f6f1ff; border: 1px solid #e2d6ff; border-radius: 14px; padding: 14px 16px; }
    .assessment-table th, .assessment-table td { vertical-align: top; font-size: 13.5px; white-space: normal; }
    .assessment-table th { font-weight: 700; color: #4b3a78; }
    .assessment-badge { font-size: 12px; padding: .38rem .55rem; border-radius: 999px; }
</style>
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Penilaian belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-4 assessment-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="assessment-section-title mb-1">Form Penilaian Pembimbing Lapangan</h6>
            <div class="text-muted small">Nilai akhir dihitung otomatis oleh sistem.</div>
        </div>
        <a href="{{ route('pembimbing.penilaian.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @php
        $statusNilai = $penilaian?->statusNilaiLabel() ?? 'Belum Lengkap';
        $statusBadge = $statusNilai === 'Final' ? 'success' : ($statusNilai === 'Sementara' ? 'warning text-dark' : 'secondary');
    @endphp
    <div class="assessment-info mb-4">
        <div class="row g-3">
            <div class="col-md-3"><div class="text-muted small">Nama Mahasiswa</div><div class="fw-semibold">{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</div></div>
            <div class="col-md-2"><div class="text-muted small">NIM</div><div class="fw-semibold">{{ $pengajuan->mahasiswa->nim ?? '-' }}</div></div>
            <div class="col-md-3"><div class="text-muted small">Program Studi</div><div class="fw-semibold">{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Tempat Magang</div><div class="fw-semibold">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Pembimbing Lapangan</div><div class="fw-semibold">{{ auth()->user()->pembimbingLapangan?->nama_pembimbing ?? '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Status Seminar</div><div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $pengajuan->status_seminar ?: 'belum')) }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Status Nilai</div><span class="badge bg-{{ $statusBadge }} assessment-badge">{{ $statusNilai }}</span></div>
        </div>
    </div>

    @if($pengajuan->mahasiswa?->absensiAktif() && isset($rekapAbsensi))
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
        <div class="alert alert-warning mb-0">{{ $lockMessage ?? 'Penilaian belum dapat diproses.' }}</div>
    @else
    <form action="{{ route('pembimbing.penilaian.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="alert alert-info py-2 small">Nilai sementara akan diperbarui setelah nilai seminar hasil magang diinput.</div>
        <div class="row g-3">
            @php
                $fields = \App\Models\Penilaian::tahap1Fields('pembimbing');
            @endphp
            <div class="col-12"><div class="assessment-section-title">Penilaian Tahap 1 - Prestasi Praktik Kerja Lapangan/Magang</div></div>
            @foreach($fields as $name => $label)
            <div class="col-md-6">
                <label class="form-label fw-semibold">{{ $loop->iteration }}. {{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" min="0" max="100" value="{{ old($name, $penilaian?->{$name}) }}" required>
                @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endforeach
            <div class="col-md-6">
                <label class="form-label fw-semibold">File Penilaian Formal <span class="text-muted small">(PDF, opsional)</span></label>
                <input type="file" name="file_penilaian_formal" class="form-control @error('file_penilaian_formal') is-invalid @enderror" accept=".pdf">
                <small class="text-muted">Template penilaian formal tersedia di menu Pedoman & SOP.</small>
                @error('file_penilaian_formal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($penilaian?->file_penilaian_formal_pembimbing)
                    <div class="mt-2"><a href="{{ route('pembimbing.penilaian.file', $pengajuan->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat File Saat Ini</a></div>
                @endif
            </div>
            <div class="col-12">
                <hr>
                <h6 class="assessment-section-title mb-1">Penilaian Tahap 2 - Seminar Hasil Magang</h6>
                <div class="text-muted small">Kurang Memuaskan: 50-60 &bull; Memuaskan: 70-80 &bull; Sangat Memuaskan: 90-100.</div>
                <div class="small text-muted mt-1">Status seminar: <strong>{{ ucwords(str_replace('_', ' ', $pengajuan->status_seminar ?: 'belum')) }}</strong> / Jadwal: <strong>{{ $pengajuan->seminar_tanggal ?: '-' }} {{ $pengajuan->seminar_jam ?: '' }}</strong></div>
            </div>
            @if($pengajuan->status_seminar === 'selesai')
                <div class="col-12">
                    <div class="fw-semibold mb-2">Penilaian Laporan PKL/Magang</div>
                    <div class="table-responsive">
                        <table class="table table-sm assessment-table">
                            <thead class="table-light"><tr><th class="align-top" style="width:60px;">No</th><th class="align-top">Indikator</th><th class="align-top" style="width:90px;">Bobot</th><th class="align-top" style="width:160px;">Nilai</th></tr></thead>
                            <tbody>
                                @foreach(\App\Models\Penilaian::laporanRubrik('pembimbing') as $name => [$label, $bobot])
                                    <tr>
                                        <td class="align-top">{{ $loop->iteration }}</td>
                                        <td class="align-top">{{ $label }}</td>
                                        <td class="align-top">{{ $bobot }}%</td>
                                        <td class="align-top">
                                            <input type="number" name="{{ $name }}" class="form-control form-control-sm @error($name) is-invalid @enderror" min="50" max="100" value="{{ old($name, $penilaian?->{$name}) }}">
                                            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <div class="fw-semibold mb-2">Penilaian Presentasi Laporan PKL/Magang</div>
                    <div class="table-responsive">
                        <table class="table table-sm assessment-table">
                            <thead class="table-light"><tr><th class="align-top" style="width:60px;">No</th><th class="align-top">Indikator</th><th class="align-top" style="width:90px;">Bobot</th><th class="align-top" style="width:160px;">Nilai</th></tr></thead>
                            <tbody>
                                @foreach(\App\Models\Penilaian::presentasiRubrik('pembimbing') as $name => [$label, $bobot])
                                    <tr>
                                        <td class="align-top">{{ $loop->iteration + 10 }}</td>
                                        <td class="align-top">{{ $label }}</td>
                                        <td class="align-top">{{ $bobot }}%</td>
                                        <td class="align-top">
                                            <input type="number" name="{{ $name }}" class="form-control form-control-sm @error($name) is-invalid @enderror" min="50" max="100" value="{{ old($name, $penilaian?->{$name}) }}">
                                            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-12">
                    <div class="alert alert-warning mb-0">Nilai seminar hasil magang dapat diinput setelah admin menyelesaikan status seminar. Nilai Tahap 1 tetap dapat disimpan sebagai nilai sementara.</div>
                </div>
            @endif
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Pembimbing Lapangan</label>
                <textarea name="catatan_mitra" class="form-control @error('catatan_mitra') is-invalid @enderror" rows="3">{{ old('catatan_mitra', $penilaian->catatan_mitra ?? '') }}</textarea>
                @error('catatan_mitra')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @if($penilaian)
        <div class="alert alert-info mt-4 mb-0 py-2 small">
            Nilai Tahap 1 Pembimbing Lapangan: <strong>{{ $penilaian->nilai_tahap1_pembimbing !== null ? number_format($penilaian->nilai_tahap1_pembimbing, 2) : 'Belum tersedia' }}</strong>
            / Nilai Tahap 2 Pembimbing: <strong>{{ $penilaian->nilai_seminar_pembimbing !== null ? number_format($penilaian->nilai_seminar_pembimbing, 2) : 'Belum tersedia' }}</strong>
            / Status: <strong>{{ $penilaian->statusNilaiLabel() }}</strong>
        </div>
        @endif

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">{{ $penilaian ? 'Update Nilai' : 'Simpan Nilai' }}</button>
            <a href="{{ route('pembimbing.penilaian.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @endunless
</div>
@endsection
