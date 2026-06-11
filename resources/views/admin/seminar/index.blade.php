@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar Pengajuan Seminar</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Mahasiswa</th><th>Instansi</th><th>Kelayakan</th><th>Judul</th><th>Status</th><th>Jadwal</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($pengajuans as $pengajuan)
                @php($badge = ['menunggu_jadwal'=>'warning','terjadwal'=>'success','selesai'=>'primary','ditunda'=>'secondary','dibatalkan'=>'dark'])
                <tr>
                    <td><strong>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</strong><div class="small text-muted">{{ $pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                    <td>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td>
                        Dosen: {{ $pengajuan->kelayakanSeminar->status_persetujuan_dosen ?? '-' }}<br>
                        Pembimbing: {{ $pengajuan->kelayakanSeminar->status_persetujuan_pembimbing ?? '-' }}
                    </td>
                    <td>{{ $pengajuan->judul_laporan ?? '-' }}</td>
                    <td><span class="badge bg-{{ $badge[$pengajuan->status_seminar] ?? 'secondary' }}">{{ $pengajuan->status_seminar }}</span></td>
                    <td>{{ $pengajuan->seminar_tanggal ?? '-' }}<br>{{ $pengajuan->seminar_jam ?? '-' }}<br>{{ $pengajuan->seminar_ruangan ?? '-' }}</td>
                    <td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#jadwal-{{ $pengajuan->id }}">Jadwalkan/Ubah</button></td>
                </tr>
                <tr class="collapse" id="jadwal-{{ $pengajuan->id }}"><td colspan="7">
                    <form action="{{ route('admin.seminar.schedule', $pengajuan->id) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4"><label class="form-label">Judul Seminar</label><input type="text" name="judul_laporan" class="form-control" value="{{ $pengajuan->judul_laporan }}" required></div>
                        <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="seminar_tanggal" class="form-control" value="{{ $pengajuan->seminar_tanggal }}" required></div>
                        <div class="col-md-2"><label class="form-label">Jam</label><input type="time" name="seminar_jam" class="form-control" value="{{ $pengajuan->seminar_jam }}" required></div>
                        <div class="col-md-2"><label class="form-label">Ruangan</label><input type="text" name="seminar_ruangan" class="form-control" value="{{ $pengajuan->seminar_ruangan }}" required></div>
                        <div class="col-md-2"><label class="form-label">Status</label><select name="status_seminar" class="form-select" required><option value="terjadwal">Terjadwal</option><option value="selesai">Selesai</option><option value="ditunda">Ditunda</option><option value="dibatalkan">Dibatalkan</option></select></div>
                        <div class="col-12"><label class="form-label">Catatan Admin</label><textarea name="catatan" class="form-control" rows="2">{{ $pengajuan->catatan_admin }}</textarea></div>
                        <div class="col-12 text-end"><button class="btn btn-primary">Simpan Jadwal</button></div>
                    </form>
                </td></tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada pengajuan seminar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pengajuans->links() }}
</div>
@endsection