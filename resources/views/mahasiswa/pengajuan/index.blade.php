@extends('layouts.app')
@section('title', 'Pengajuan')
@section('page-title', 'Pengajuan')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Daftar Pengajuan</h6>
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Buat Pengajuan
        </a>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th>No</th><th>Jenis</th><th>Tanggal</th><th>Instansi</th><th>Status</th><th>Catatan Admin</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{ $p->jenis_pengajuan === 'surat_keterangan' ? 'SK Magang' : 'Surat Pengantar/Rekomendasi Magang' }}
                    @if($p->jenis_pengajuan === 'surat_keterangan' && $p->status_surat_keterangan !== 'belum_ada')
                        <div class="small text-muted">Surat Keterangan: {{ $p->status_surat_keterangan }}</div>
                    @endif
                </td>
                <td>{{ $p->created_at?->format('d M Y') ?? '-' }}</td>
                <td>{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual ?? '-' }}</td>
                <td>
                    @php $badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger','berjalan'=>'info','selesai'=>'secondary','dibatalkan'=>'dark']; @endphp
                    <span class="badge bg-{{ $badge[$p->status_pengajuan] ?? 'secondary' }}">{{ $p->status_pengajuan }}</span>
                </td>
                <td>{{ $p->catatan_admin ?? '-' }}</td>
                <td>
                    <a href="{{ route('mahasiswa.pengajuan.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    @if(in_array($p->status_pengajuan, ['pending','revisi'], true))
                    <form action="{{ route('mahasiswa.pengajuan.cancel', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada pengajuan</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
