@extends('layouts.app')
@section('title', 'Bimbingan')
@section('page-title', 'Bimbingan')

@section('content')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar Bimbingan</h6>
    <div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th class="align-top">No</th><th class="align-top">Mahasiswa</th><th class="align-top">NIM</th><th class="align-top">Program Studi</th><th class="align-top">Instansi</th><th class="align-top">Status Magang</th><th class="align-top">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($bimbingans as $i => $b)
            <tr style="vertical-align: top;">
                <td class="align-top">{{ $i + 1 }}</td>
                <td class="align-top">{{ $b->pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td class="align-top">{{ $b->pengajuan->mahasiswa->nim ?? '-' }}</td>
                <td class="align-top">{{ $b->pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td class="align-top">{{ $b->pengajuan->mitra->nama_instansi ?? $b->pengajuan->nama_instansi_manual ?? '-' }}</td>
                <td class="align-top"><span class="badge bg-{{ $b->pengajuan->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ $b->pengajuan->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</span></td>
                <td class="align-top">
                    <a href="{{ route('dosen.bimbingan.formal.index', $b->pengajuan_id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada bimbingan</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection

