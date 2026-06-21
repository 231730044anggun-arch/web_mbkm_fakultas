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
    $formatStatus = fn($status) => ucwords(str_replace('_', ' ', $status ?: 'pending'));
@endphp

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">{{ $isDetail ? 'Logbook - ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') : 'Logbook Mahasiswa Bimbingan' }}</h6>
            <div class="text-muted small">{{ $isDetail ? 'Daftar logbook mahasiswa yang dipilih.' : 'Pilih mahasiswa bimbingan untuk melihat dan memvalidasi logbook.' }}</div>
        </div>
        @if($isDetail)
            <a href="{{ route('dosen.logbook.export', $pengajuan->id) }}?from={{ request('from') }}&to={{ request('to') }}&status={{ request('status') }}" class="btn btn-sm btn-outline-secondary">Export PDF</a>
        @endif
    </div>

    @unless($isDetail)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Nama Mahasiswa</th>
                    <th class="align-top">NIM</th>
                    <th class="align-top">Program Studi</th>
                    <th class="align-top">Instansi/Mitra</th>
                    <th class="align-top">Periode Magang</th>
                    <th class="align-top">Jumlah Logbook</th>
                    <th class="align-top">Status Kelengkapan</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $item)
                <tr>
                    <td class="align-top">{{ $item->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td class="align-top">{{ $item->mahasiswa->nim ?? '-' }}</td>
                    <td class="align-top">{{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td class="align-top">{{ $item->mitra->nama_instansi ?? $item->nama_instansi_manual ?? '-' }}</td>
                    <td class="align-top">{{ $item->tanggal_mulai ?? '-' }} s/d {{ $item->tanggal_selesai ?? '-' }}</td>
                    <td class="align-top">{{ $item->logbooks_count }}</td>
                    <td class="align-top">
                        @if($item->logbooks_count < 1)
                            <span class="badge bg-secondary">Belum ada</span>
                        @elseif($item->logbooks_disetujui_count === $item->logbooks_count)
                            <span class="badge bg-success">Disetujui</span>
                        @else
                            <span class="badge bg-warning text-dark">Proses validasi</span>
                        @endif
                    </td>
                    <td class="align-top"><a href="{{ route('dosen.logbook.show', $item->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mahasiswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
    @else

    <form class="row g-2 mb-3" action="{{ $filterRoute }}" method="GET">
        <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
        <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status Dosen Pembimbing</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="disetujui" {{ request('status')=='disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="revisi" {{ request('status')=='revisi' ? 'selected' : '' }}>Revisi</option>
            </select>
        </div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Filter</button></div>
    </form>

    @if(!empty($missing) && count($missing))
    <div class="alert alert-warning">Logbook mingguan belum lengkap. Sistem mendeteksi minggu yang belum memiliki logbook lengkap dan disetujui dosen pembimbing serta pembimbing lapangan: {{ implode(', ', $missing) }}.</div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Mahasiswa</th>
                    <th class="align-top">Mitra/Instansi</th>
                    <th class="align-top">Tanggal/Jam</th>
                    <th class="align-top">Kegiatan</th>
                    <th class="align-top">Output/Kendala/Solusi</th>
                    <th class="align-top">Bukti</th>
                    <th class="align-top">Status Dosen Pembimbing</th>
                    <th class="align-top">Status Pembimbing Lapangan</th>
                    <th class="align-top">Catatan Dosen Pembimbing</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbooks as $l)
                @php $rowPengajuan = $l->pengajuan; @endphp
                <tr>
                    <td class="align-top">
                        <div class="fw-semibold">{{ $rowPengajuan->mahasiswa->nama_lengkap ?? '-' }}</div>
                        <div class="small text-muted">{{ $rowPengajuan->mahasiswa->nim ?? '-' }} / {{ $rowPengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div>
                    </td>
                    <td class="align-top">{{ $rowPengajuan->mitra->nama_instansi ?? $rowPengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td class="align-top"><div>{{ $l->tanggal }}</div><div class="small text-muted">{{ $l->jam_mulai }} - {{ $l->jam_selesai }}</div></td>
                    <td class="align-top">{{ Str::limit($l->kegiatan, 70) }}</td>
                    <td class="align-top">
                        <div><strong>Output:</strong> {{ Str::limit($l->output_kegiatan ?: 'Tidak ada', 70) }}</div>
                        <div class="small text-muted"><strong>Kendala:</strong> {{ Str::limit($l->kendala ?: 'Tidak ada', 70) }}</div>
                        <div class="small text-muted"><strong>Solusi:</strong> {{ Str::limit($l->solusi ?: 'Tidak ada', 70) }}</div>
                    </td>
                    <td class="align-top">
                        @if($l->bukti_foto)
                            <a href="{{ route('logbook.bukti.preview', $l->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        @else
                            <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td class="align-top"><span class="badge bg-{{ $badge[$l->status_dosen ?? 'pending'] ?? 'secondary' }}">{{ $formatStatus($l->status_dosen ?? 'pending') }}</span></td>
                    <td class="align-top"><span class="badge bg-{{ $badge[$l->status_mitra ?? 'pending'] ?? 'secondary' }}">{{ $formatStatus($l->status_mitra ?? 'pending') }}</span></td>
                    <td class="align-top">{{ $l->catatan_dosen ?: 'Tidak ada' }}</td>
                    <td class="align-top" style="min-width:180px">
                        @if(($l->status_dosen ?? 'pending') !== 'disetujui')
                        <form action="{{ route('dosen.logbook.validasi', $l->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="disetujui">
                            <button class="btn btn-sm btn-success">Setujui</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#revisi-dosen-{{ $l->id }}">Minta Revisi</button>
                        <div class="collapse mt-2" id="revisi-dosen-{{ $l->id }}">
                            <form action="{{ route('dosen.logbook.validasi', $l->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="revisi">
                                <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan revisi dosen" required>{{ old('catatan') }}</textarea>
                                <button class="btn btn-sm btn-danger">Kirim Revisi</button>
                            </form>
                        </div>
                        @else
                        <span class="badge bg-success">Sudah Disetujui</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada logbook mahasiswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logbooks->links() }}
    @endunless
</div>
@endsection

