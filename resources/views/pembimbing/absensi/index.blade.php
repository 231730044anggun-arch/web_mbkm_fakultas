@extends('layouts.app')
@section('title', 'Absensi Mahasiswa')
@section('page-title', 'Absensi Mahasiswa')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger"><strong>Validasi belum bisa disimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Rekap Absensi Mahasiswa Magang</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light"><tr><th>Mahasiswa</th><th>Hari Wajib</th><th>Masuk</th><th>Disetujui</th><th>Pending</th><th>Revisi/Tolak</th><th>Kehadiran Valid</th></tr></thead>
            <tbody>
            @forelse($pengajuans as $pengajuan)
                @php($rekap = $rekaps[$pengajuan->id])
                <tr>
                    <td><div class="fw-semibold">{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</div><div class="small text-muted">NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }} / Program Studi: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</div></td>
                    <td>{{ $rekap['total_hari_wajib'] }}</td>
                    <td>{{ $rekap['jumlah_absensi_masuk'] }}</td>
                    <td>{{ $rekap['jumlah_disetujui'] }}</td>
                    <td>{{ $rekap['jumlah_pending'] }}</td>
                    <td>{{ $rekap['jumlah_revisi'] }}</td>
                    <td><strong>{{ $rekap['persentase_kehadiran'] }}%</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada mahasiswa magang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h6 class="fw-bold mb-1">Validasi Absensi</h6><div class="text-muted small">Hanya absensi mahasiswa yang magang di mahasiswa bimbingan Anda.</div></div>
        <form class="row g-2" method="GET" action="{{ route('pembimbing.absensi.index') }}">
            <div class="col-auto"><select name="mahasiswa_id" class="form-select form-select-sm"><option value="">Semua Mahasiswa</option>@foreach($pengajuans as $pengajuan)<option value="{{ $pengajuan->mahasiswa_id }}" @selected(request('mahasiswa_id') == $pengajuan->mahasiswa_id)>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</option>@endforeach</select></div>
            <div class="col-auto"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><select name="status" class="form-select form-select-sm"><option value="">Semua Status</option><option value="pending" @selected(request('status')==='pending')>Pending</option><option value="disetujui" @selected(request('status')==='disetujui')>Disetujui</option><option value="revisi" @selected(request('status')==='revisi')>Revisi</option><option value="ditolak" @selected(request('status')==='ditolak')>Ditolak</option></select></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Mahasiswa</th><th>Tanggal</th><th>Jam</th><th>Keterangan</th><th>Bukti</th><th>Status</th><th>Catatan Pembimbing</th><th>Aksi</th></tr></thead>
            <tbody>
            @php($badge = ['pending'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger'])
            @forelse($absensis as $absensi)
                <tr>
                    <td><div class="fw-semibold">{{ $absensi->mahasiswa->nama_lengkap ?? '-' }}</div><div class="small text-muted">NIM: {{ $absensi->mahasiswa->nim ?? '-' }} / Program Studi: {{ $absensi->mahasiswa->prodi->nama_prodi ?? '-' }}</div></td>
                    <td>{{ $absensi->tanggal->format('Y-m-d') }}</td>
                    <td>{{ $absensi->jam_masuk }} - {{ $absensi->jam_pulang ?: '-' }}</td>
                    <td>{{ $absensi->keterangan ?: 'Tidak ada' }}</td>
                    <td><a href="{{ route('pembimbing.absensi.preview', $absensi->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
                    <td><span class="badge bg-{{ $badge[$absensi->status] ?? 'secondary' }}">{{ ucfirst($absensi->status) }}</span></td>
                    <td>{{ $absensi->catatan_mitra ?: 'Tidak ada' }}</td>
                    <td style="min-width:260px">
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            @if($absensi->status !== 'disetujui')
                            <form action="{{ route('pembimbing.absensi.validasi', $absensi->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="disetujui">
                                <button class="btn btn-sm btn-success">Disetujui</button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#absensi-revisi-{{ $absensi->id }}">Revisi</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#absensi-ditolak-{{ $absensi->id }}">Ditolak</button>
                        </div>
                        <div class="collapse mt-2" id="absensi-revisi-{{ $absensi->id }}">
                            <form action="{{ route('pembimbing.absensi.validasi', $absensi->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="revisi">
                                <textarea name="catatan_mitra" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan wajib untuk revisi" required></textarea>
                                <button class="btn btn-sm btn-warning">Kirim Revisi</button>
                            </form>
                        </div>
                        <div class="collapse mt-2" id="absensi-ditolak-{{ $absensi->id }}">
                            <form action="{{ route('pembimbing.absensi.validasi', $absensi->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="ditolak">
                                <textarea name="catatan_mitra" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan wajib untuk penolakan" required></textarea>
                                <button class="btn btn-sm btn-danger">Kirim Penolakan</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada absensi mahasiswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $absensis->links() }}
</div>
@endsection


