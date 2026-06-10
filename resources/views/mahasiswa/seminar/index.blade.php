@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card p-4">
    <h6 class="fw-bold mb-3">Status dan Pengajuan Seminar</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Pengajuan</th><th>Status Seminar</th><th>Jadwal</th><th>Pembimbing/Penguji</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $pengajuan)
                @php
                    $check = $eligibility[$pengajuan->id] ?? ['allowed' => false, 'reasons' => []];
                    $badge = ['menunggu'=>'warning','terjadwal'=>'success','selesai'=>'primary','ditolak'=>'danger','revisi'=>'danger','ditunda'=>'secondary','belum'=>'secondary','dibatalkan'=>'dark'];
                @endphp
                <tr>
                    <td>
                        <strong>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</strong>
                        <div class="small text-muted">{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</div>
                        @if($pengajuan->judul_laporan)
                            <div class="small">Judul: {{ $pengajuan->judul_laporan }}</div>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $badge[$pengajuan->status_seminar] ?? 'secondary' }}">{{ $pengajuan->status_seminar ?? 'belum' }}</span></td>
                    <td>
                        @if($pengajuan->seminar_tanggal)
                            {{ $pengajuan->seminar_tanggal }}<br>
                            {{ $pengajuan->seminar_jam ?? '-' }}<br>
                            {{ $pengajuan->seminar_ruangan ?? '-' }}
                        @else
                            <span class="text-muted">Belum dijadwalkan</span>
                        @endif
                    </td>
                    <td>
                        @forelse($pengajuan->bimbingans as $bimbingan)
                            <div>{{ $bimbingan->dosen->nama_dosen ?? '-' }}</div>
                        @empty
                            <span class="text-muted">Belum ditugaskan</span>
                        @endforelse
                    </td>
                    <td>
                        @if(in_array($pengajuan->status_seminar, ['terjadwal', 'selesai'], true))
                            <a href="{{ route('mahasiswa.seminar.surat', $pengajuan->id) }}" class="btn btn-sm btn-outline-success">Download Surat/SK</a>
                        @elseif($pengajuan->status_seminar === 'menunggu')
                            <form action="{{ route('mahasiswa.seminar.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan seminar ini?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Batalkan Seminar</button>
                            </form>
                        @elseif($check['allowed'])
                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#seminar-form-{{ $pengajuan->id }}">Ajukan Seminar</button>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#seminar-reason-{{ $pengajuan->id }}">Lihat Syarat</button>
                        @endif
                    </td>
                </tr>
                <tr class="collapse" id="seminar-reason-{{ $pengajuan->id }}">
                    <td colspan="5">
                        <div class="alert alert-warning mb-0">
                            <strong>Belum bisa mengajukan seminar:</strong>
                            <ul class="mb-0">
                                @foreach($check['reasons'] as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr class="collapse" id="seminar-form-{{ $pengajuan->id }}">
                    <td colspan="5">
                        <form action="{{ route('mahasiswa.seminar.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                            @csrf
                            <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">
                            <div class="col-md-8">
                                <label class="form-label">Judul Laporan</label>
                                <input type="text" name="judul_laporan" class="form-control" value="{{ old('judul_laporan', $pengajuan->judul_laporan) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Laporan</label>
                                @php($laporan = $pengajuan->dokumens->where('jenis_dokumen', 'laporan')->where('status_verifikasi', 'valid')->last())
                                <input type="text" class="form-control" value="{{ $laporan ? 'Laporan valid sudah tersedia' : 'Belum ada laporan valid' }}" disabled>
                            </div>
                            @if(!$laporan)
                            <div class="col-md-6">
                                <label class="form-label">Upload Laporan</label>
                                <input type="file" name="file_laporan" class="form-control" accept=".pdf,.doc,.docx">
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Usulan Tanggal Seminar</label>
                                <input type="date" name="usulan_tanggal_seminar" class="form-control" value="{{ old('usulan_tanggal_seminar', $pengajuan->usulan_tanggal_seminar) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ringkasan/Topik Seminar</label>
                                <textarea name="ringkasan_seminar" class="form-control" rows="2">{{ old('ringkasan_seminar', $pengajuan->ringkasan_seminar) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-primary">Kirim Pengajuan Seminar</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada pengajuan magang yang bisa diproses seminar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
