@extends('layouts.app')
@section('title', 'Pengajuan')
@section('page-title', 'Pengajuan')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold">Daftar Pengajuan</h6>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Mahasiswa</th>
                <th>Jenis</th>
                <th>Instansi</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>
                    {{ $p->jenis_pengajuan === 'surat_keterangan' ? 'SK Magang' : 'Surat Pengantar/Rekomendasi' }}
                    @if($p->jenis_pengajuan === 'surat_keterangan')
                        <div class="small text-muted">Surat Keterangan: {{ ucwords(str_replace('_', ' ', $p->status_surat_keterangan ?? 'belum_ada')) }}</div>
                    @endif
                </td>
                <td>{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual ?? '-' }}</td>
                <td>{{ $p->periode->nama_periode ?? '-' }}</td>
                <td>
                    @php
                        $badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger','berjalan'=>'info','selesai'=>'secondary','dibatalkan'=>'dark'];
                    @endphp
                    <span class="badge bg-{{ $badge[$p->status_pengajuan] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $p->status_pengajuan)) }}</span>
                </td>
                <td>
                    <a href="{{ route('admin.pengajuan.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    <form action="{{ route('admin.pengajuan.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengajuan ini? Pengajuan yang sudah punya riwayat tidak akan dihapus.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada pengajuan</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $pengajuans->links() }}
</div>
@endsection
