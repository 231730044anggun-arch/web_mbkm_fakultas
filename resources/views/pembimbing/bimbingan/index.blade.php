@extends('layouts.app')
@section('title', 'Bimbingan')
@section('page-title', 'Bimbingan Mahasiswa')

@section('content')
@include('partials.alerts')
<div class="card p-4">
    <h6 class="fw-bold mb-3">Bimbingan Mahasiswa ke Pembimbing Lapangan</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="align-top">Mahasiswa</th>
                    <th class="align-top">Program Studi</th>
                    <th class="align-top">Mitra/Instansi</th>
                    <th class="align-top">Bimbingan Masuk</th>
                    <th class="align-top">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $pengajuan)
                    <tr style="vertical-align: top;">
                        <td class="align-top"><strong>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</strong><div class="text-muted small">{{ $pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                        <td class="align-top">{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                        <td class="align-top">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td>
                        <td class="align-top">{{ $pengajuan->bimbinganFormals->count() }}</td>
                        <td class="align-top">
                            <a href="{{ route('pembimbing.bimbingan.formal.index', $pengajuan->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada bimbingan mahasiswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection
