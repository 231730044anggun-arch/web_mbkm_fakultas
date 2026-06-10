@extends('layouts.app')
@section('title', 'Validasi Logbook')
@section('page-title', 'Validasi Logbook')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <strong>Validasi belum bisa disimpan.</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@php
    $isDetail = filled($pengajuan);
    $filterRoute = $isDetail ? route('dosen.logbook.show', $pengajuan->id) : route('dosen.logbook.index');
    $badge = ['pending' => 'warning', 'disetujui' => 'success', 'revisi' => 'danger'];
@endphp

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">{{ $isDetail ? 'Logbook - ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') : 'Logbook Mahasiswa Bimbingan' }}</h6>
            <div class="text-muted small">Hanya menampilkan logbook mahasiswa yang ditugaskan kepada Anda.</div>
        </div>
        @if($isDetail)
            <a href="{{ route('dosen.logbook.export', $pengajuan->id) }}?from={{ request('from') }}&to={{ request('to') }}&status={{ request('status') }}" class="btn btn-sm btn-outline-secondary">Export PDF</a>
        @endif
    </div>

    <form class="row g-2 mb-3" action="{{ $filterRoute }}" method="GET">
        <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
        <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status Dosen</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="disetujui" {{ request('status')=='disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="revisi" {{ request('status')=='revisi' ? 'selected' : '' }}>Revisi</option>
            </select>
        </div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Filter</button></div>
    </form>

    @if(!empty($missing) && count($missing))
    <div class="alert alert-warning">Logbook mingguan belum lengkap. Sistem mendeteksi minggu yang belum memiliki logbook lengkap dan disetujui dosen serta mitra: {{ implode(', ', $missing) }}.</div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mahasiswa</th>
                    <th>Mitra</th>
                    <th>Tanggal/Jam</th>
                    <th>Kegiatan</th>
                    <th>Output/Kendala/Solusi</th>
                    <th>Bukti</th>
                    <th>Status Dosen</th>
                    <th>Status Mitra</th>
                    <th>Catatan Dosen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbooks as $l)
                @php $rowPengajuan = $l->pengajuan; @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $rowPengajuan->mahasiswa->nama_lengkap ?? '-' }}</div>
                        <div class="small text-muted">{{ $rowPengajuan->mahasiswa->nim ?? '-' }} / {{ $rowPengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
                    </td>
                    <td>{{ $rowPengajuan->mitra->nama_instansi ?? $rowPengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td><div>{{ $l->tanggal }}</div><div class="small text-muted">{{ $l->jam_mulai }} - {{ $l->jam_selesai }}</div></td>
                    <td>{{ Str::limit($l->kegiatan, 70) }}</td>
                    <td>
                        <div><strong>Output:</strong> {{ Str::limit($l->output_kegiatan ?: 'Tidak ada', 70) }}</div>
                        <div class="small text-muted"><strong>Kendala:</strong> {{ Str::limit($l->kendala ?: 'Tidak ada', 70) }}</div>
                        <div class="small text-muted"><strong>Solusi:</strong> {{ Str::limit($l->solusi ?: 'Tidak ada', 70) }}</div>
                    </td>
                    <td>
                        @if($l->bukti_foto)
                            <a href="{{ route('dosen.logbook.foto.preview', $l->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        @else
                            <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $badge[$l->status_dosen ?? 'pending'] ?? 'secondary' }}">{{ $l->status_dosen ?? 'pending' }}</span></td>
                    <td><span class="badge bg-{{ $badge[$l->status_mitra ?? 'pending'] ?? 'secondary' }}">{{ $l->status_mitra ?? 'pending' }}</span></td>
                    <td>{{ $l->catatan_dosen ?: 'Tidak ada' }}</td>
                    <td style="min-width:180px">
                        @if(($l->status_dosen ?? 'pending') !== 'disetujui')
                        <form action="{{ route('dosen.logbook.validasi', $l->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="disetujui">
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#revisi-dosen-{{ $l->id }}">Revisi</button>
                        <div class="collapse mt-2" id="revisi-dosen-{{ $l->id }}">
                            <form action="{{ route('dosen.logbook.validasi', $l->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="revisi">
                                <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan revisi dosen" required>{{ old('catatan') }}</textarea>
                                <button class="btn btn-sm btn-danger">Kirim Revisi</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada logbook mahasiswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logbooks->links() }}
</div>
@endsection

