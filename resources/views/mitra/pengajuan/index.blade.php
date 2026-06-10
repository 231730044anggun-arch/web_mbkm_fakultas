@extends('layouts.app')
@section('title', 'Mahasiswa Magang')
@section('page-title', 'Mahasiswa Magang')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Daftar Mahasiswa Magang</h6>
            <div class="text-muted small">Mahasiswa yang terhubung ke instansi Anda dan sudah berstatus magang aktif/selesai.</div>
        </div>
        <form method="GET" class="row g-2">
            <div class="col-auto">
                <select name="pic" class="form-select form-select-sm">
                    <option value="">Semua PIC</option>
                    @foreach($pics as $pic)
                        <option value="{{ $pic }}" @selected(request('pic') === $pic)>{{ $pic }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Program Studi</th>
                    <th>Periode</th>
                    <th>Tanggal Magang</th>
                    <th>Dosen Pembimbing</th>
                    <th>Pembimbing Lapangan/PIC</th>
                    <th>Status Magang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $i => $p)
                <tr>
                    <td>{{ $pengajuans->firstItem() + $i }}</td>
                    <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $p->mahasiswa->nim ?? '-' }}</td>
                    <td>{{ $p->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $p->periode->nama_periode ?? '-' }}</td>
                    <td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                    <td>{{ $p->bimbingans->first()?->dosen?->nama_dosen ?? '-' }}</td>
                    <td>{{ $p->pic_nama ?? $p->mitra->pembimbing_lapangan_nama ?? '-' }}</td>
                    <td><span class="badge bg-{{ $p->status_pengajuan === 'berjalan' ? 'success' : 'secondary' }}">{{ $p->status_pengajuan === 'berjalan' ? 'Aktif' : 'Selesai' }}</span></td>
                    <td><a href="{{ route('mitra.pengajuan.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada mahasiswa magang</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection