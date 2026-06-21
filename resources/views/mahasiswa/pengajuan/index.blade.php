@extends('layouts.app')
@section('title', 'Pengajuan')
@section('page-title', 'Pengajuan')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="student-card-title">Daftar Pengajuan</h6>
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Buat Pengajuan
        </a>
    </div>
    <div class="table-responsive">
    <table class="table table-hover student-table">
        <thead class="table-light">
            <tr>
                <th class="align-top">No</th>
                <th class="align-top">Jenis</th>
                <th class="align-top">Tanggal</th>
                <th class="align-top">Instansi</th>
                <th class="align-top">Status</th>
                <th class="align-top">Catatan Admin</th>
                <th class="align-top">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $i => $p)
            <tr>
                <td class="align-top">{{ $i + 1 }}</td>
                <td class="align-top">
                    {{ $p->jenis_pengajuan === 'surat_keterangan' ? 'SK Magang' : 'Surat Pengantar/Rekomendasi Magang' }}
                    @if($p->jenis_pengajuan === 'surat_keterangan' && $p->status_surat_keterangan !== 'belum_ada')
                        <div class="small text-muted">Surat Keterangan: {{ ucwords(str_replace('_', ' ', $p->status_surat_keterangan)) }}</div>
                    @endif
                </td>
                <td class="align-top">{{ $p->created_at?->format('d M Y') ?? '-' }}</td>
                <td class="align-top">{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual ?? '-' }}</td>
                <td class="align-top">
                    @php $badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger','berjalan'=>'info','selesai'=>'secondary','dibatalkan'=>'dark']; @endphp
                    <span class="badge bg-{{ $badge[$p->status_pengajuan] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $p->status_pengajuan)) }}</span>
                </td>
                <td class="align-top">{{ $p->catatan_admin ?? '-' }}</td>
                <td class="align-top">
                    <div class="student-action-buttons">
                    <a href="{{ route('mahasiswa.pengajuan.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    @if(in_array($p->status_pengajuan, ['pending','revisi'], true))
                    <form action="{{ route('mahasiswa.pengajuan.cancel', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                    </form>
                    @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada pengajuan</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
