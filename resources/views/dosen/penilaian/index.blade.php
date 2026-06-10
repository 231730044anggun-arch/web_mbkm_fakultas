@extends('layouts.app')
@section('title', 'Penilaian')
@section('page-title', 'Penilaian Akademik')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Daftar Penilaian Akademik Mahasiswa Bimbingan</h6>
            <div class="text-muted small">Input nilai akademik dibuka setelah mahasiswa mengajukan Seminar Magang.</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>Mitra</th>
                    <th>Periode</th>
                    <th>Status Seminar</th>
                    <th>Nilai Akademik</th>
                    <th>Nilai Akhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $pengajuan)
                @php($canInput = $pengajuan->hasValidSeminar())
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</div>
                        <div class="small text-muted">NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }}</div>
                    </td>
                    <td>{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td>{{ $pengajuan->periode->nama_periode ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $canInput ? 'success' : 'secondary' }}">{{ $pengajuan->status_seminar ?: 'belum' }}</span>
                    </td>
                    <td>{{ $pengajuan->penilaian?->nilai_dosen !== null ? number_format($pengajuan->penilaian->nilai_dosen, 2) : 'Belum ada' }}</td>
                    <td>
                        @if($pengajuan->penilaian?->nilai_akhir !== null)
                            {{ number_format($pengajuan->penilaian->nilai_akhir, 2) }} ({{ $pengajuan->penilaian->grade }})
                        @else
                            <span class="text-muted small">Nilai Akhir belum tersedia</span>
                        @endif
                    </td>
                    <td>
                        @if($canInput)
                            <a href="{{ route('dosen.penilaian.create', $pengajuan->id) }}" class="btn btn-sm btn-primary">Isi/Edit Nilai Akademik</a>
                        @else
                            <span class="badge bg-light text-dark border">Terkunci</span>
                            <div class="small text-muted mt-1">Penilaian belum dapat dilakukan karena mahasiswa belum mengajukan Seminar Magang.</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mahasiswa yang dapat dinilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection
