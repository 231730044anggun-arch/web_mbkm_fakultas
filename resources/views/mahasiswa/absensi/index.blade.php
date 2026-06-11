@extends('layouts.app')
@section('title', 'Absensi Magang')
@section('page-title', 'Absensi Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Absensi belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Hari Wajib</div><h4 class="fw-bold mb-0">{{ $rekap['total_hari_wajib'] }}</h4></div></div>
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Absensi Masuk</div><h4 class="fw-bold mb-0">{{ $rekap['jumlah_absensi_masuk'] }}</h4></div></div>
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Disetujui</div><h4 class="fw-bold mb-0 text-success">{{ $rekap['jumlah_disetujui'] }}</h4></div></div>
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Pending</div><h4 class="fw-bold mb-0 text-warning">{{ $rekap['jumlah_pending'] }}</h4></div></div>
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Revisi/Tolak</div><h4 class="fw-bold mb-0 text-danger">{{ $rekap['jumlah_revisi'] }}</h4></div></div>
    <div class="col-md-2"><div class="card p-3 h-100"><div class="text-muted small">Kehadiran Valid</div><h4 class="fw-bold mb-0">{{ $rekap['persentase_kehadiran'] }}%</h4></div></div>
</div>

@if(!empty($missingDates))
<div class="alert alert-warning">Absensi belum lengkap. Anda belum mengisi atau belum mendapat persetujuan mitra pada tanggal: {{ implode(', ', $missingDates) }}.</div>
@endif

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Form Absensi Harian</h6>
            <div class="text-muted small">Absensi berbeda dari logbook. Absensi adalah bukti kehadiran harian selama magang.</div>
        </div>
        <span class="badge bg-light text-dark border">{{ $pengajuan->mitra->nama_instansi ?? '-' }}</span>
    </div>
    <form action="{{ route('mahasiswa.absensi.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Tanggal Hadir</label><input type="text" class="form-control" value="{{ now()->toDateString() }}" disabled><small class="text-muted">Tanggal absensi otomatis mengikuti tanggal hari ini dan tidak dapat diubah.</small></div><div class="col-md-3"><label class="form-label">Jam Masuk</label>
                <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk') }}" required>
                @error('jam_masuk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Jam Pulang</label>
                <input type="time" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang') }}">
                @error('jam_pulang')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Bukti Hadir</label>
                <input type="file" name="bukti_hadir" class="form-control @error('bukti_hadir') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                @error('bukti_hadir')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Keterangan Singkat</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2">{{ old('keterangan') }}</textarea>
                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary">Simpan Absensi</button>
            </div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h6 class="fw-bold mb-1">Riwayat Absensi</h6><div class="text-muted small">Status absensi divalidasi oleh mitra/instansi.</div></div>
        <form class="row g-2" method="GET" action="{{ route('mahasiswa.absensi.index') }}">
            <div class="col-auto"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><select name="status" class="form-select form-select-sm"><option value="">Semua Status</option><option value="pending" @selected(request('status')==='pending')>Pending</option><option value="disetujui" @selected(request('status')==='disetujui')>Disetujui</option><option value="revisi" @selected(request('status')==='revisi')>Revisi</option><option value="ditolak" @selected(request('status')==='ditolak')>Ditolak</option></select></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Tanggal</th><th>Jam</th><th>Keterangan</th><th>Bukti</th><th>Status</th><th>Catatan Mitra</th></tr></thead>
            <tbody>
            @php($badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger'])
            @forelse($absensis as $absensi)
                <tr>
                    <td>{{ $absensi->tanggal->format('Y-m-d') }}</td>
                    <td>{{ $absensi->jam_masuk }} - {{ $absensi->jam_pulang ?: '-' }}</td>
                    <td>{{ $absensi->keterangan ?: 'Tidak ada' }}</td>
                    <td><a href="{{ route('mahasiswa.absensi.preview', $absensi->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
                    <td><span class="badge bg-{{ $badge[$absensi->status] ?? 'secondary' }}">{{ ucfirst($absensi->status) }}</span></td>
                    <td>{{ $absensi->catatan_mitra ?: 'Tidak ada' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada absensi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $absensis->links() }}
</div>
@endsection
