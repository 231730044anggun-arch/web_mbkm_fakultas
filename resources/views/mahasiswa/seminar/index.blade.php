@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Pengiriman kelayakan belum berhasil.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4">
    <h6 class="student-card-title mb-3">Kelayakan dan Pengajuan Seminar</h6>
    <div class="table-responsive">
        <table class="table table-hover student-table">
            <thead class="table-light">
                <tr>
                    <th>Magang</th>
                    <th>Kelayakan Seminar</th>
                    <th>Seminar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($pengajuans as $pengajuan)
                @php
                    $kelayakan = $pengajuan->kelayakanSeminar;
                    $check = $eligibility[$pengajuan->id] ?? ['allowed' => false, 'reasons' => []];
                    $badge = ['menunggu_jadwal'=>'warning','terjadwal'=>'success','selesai'=>'success','ditunda'=>'warning','dibatalkan'=>'danger','belum'=>'secondary'];
                    $approvalBadge = ['menunggu'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger'];
                    $kelayakanBadge = ['menunggu_persetujuan'=>'warning','siap_dijadwalkan'=>'success','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger'];
                    $deadlineLabel = $kelayakan?->deadlineApprovalLabel() ?: $pengajuan->mahasiswa?->deadlineLaporanMagangLabel();
                    $terlambat = $kelayakan?->melewatiDeadline() ?? false;
                    $statusSeminar = $pengajuan->status_seminar ?? 'belum';
                    $statusSeminarLabel = ucwords(str_replace('_', ' ', $statusSeminar));
                    $sudahTerjadwal = in_array($statusSeminar, ['terjadwal', 'selesai'], true) || filled($pengajuan->seminar_tanggal);
                @endphp
                <tr>
                    <td class="align-top">
                        <strong>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</strong>
                        <div class="small text-muted">{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</div>
                        <div class="small">Pembimbing Lapangan: {{ $pengajuan->pembimbingLapangan->nama ?? $pengajuan->pic_nama ?? '-' }}</div>
                    </td>
                    <td class="align-top">
                        @if($deadlineLabel)
                            <div class="rounded-3 border px-3 py-2 mb-2 small" style="background:#f4efff;border-color:#ded2ff!important;color:#3b2678;">Deadline pengumpulan: <strong>{{ $deadlineLabel }}</strong> @if($terlambat)<span class="badge bg-danger student-badge ms-1">Terlambat</span>@endif</div>
                        @endif
                        @if($kelayakan)
                            @php $statusKelayakan = $kelayakan->status ?: 'menunggu_persetujuan'; @endphp
                            <div class="mb-1">Status Kelayakan: <span class="badge bg-{{ $kelayakanBadge[$statusKelayakan] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $statusKelayakan)) }}</span></div>
                            <div class="mb-1">Tanggal Pengiriman: <span class="small text-muted">{{ optional($kelayakan->updated_at)->format('d/m/Y H:i') }}</span></div>
                            <div class="mb-1">Dosen Pembimbing: <span class="badge bg-{{ $approvalBadge[$kelayakan->status_persetujuan_dosen] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $kelayakan->status_persetujuan_dosen)) }}</span></div>
                            <div class="mb-1">Pembimbing Lapangan: <span class="badge bg-{{ $approvalBadge[$kelayakan->status_persetujuan_pembimbing] ?? 'secondary' }} student-badge">{{ ucwords(str_replace('_', ' ', $kelayakan->status_persetujuan_pembimbing)) }}</span></div>
                            @if($kelayakan->catatan_dosen)<div class="small text-danger">Catatan Dosen Pembimbing: {{ $kelayakan->catatan_dosen }}</div>@endif
                            @if($kelayakan->catatan_pembimbing)<div class="small text-danger">Catatan Pembimbing Lapangan: {{ $kelayakan->catatan_pembimbing }}</div>@endif
                            @if($kelayakan->catatanHistories->isNotEmpty())
                                <div class="mt-2">
                                    <div class="small fw-semibold">Riwayat Catatan:</div>
                                    @foreach($kelayakan->catatanHistories as $history)
                                        <div class="small border rounded-3 p-2 mt-1">
                                            <div class="fw-semibold">{{ $history->role_pemberi }} <span class="text-muted fw-normal">{{ optional($history->created_at)->format('d/m/Y H:i') }}</span></div>
                                            <div>Status: {{ ucwords(str_replace('_', ' ', $history->status_tindakan)) }}</div>
                                            <div>Catatan: {{ $history->catatan ?: '-' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <span class="text-muted">Belum dikirim</span>
                        @endif
                    </td>
                    <td class="align-top">
                        <span class="badge bg-{{ $badge[$statusSeminar] ?? 'secondary' }} student-badge">{{ $statusSeminarLabel }}</span>
                        @if($pengajuan->judul_laporan)<div class="small">{{ $pengajuan->judul_laporan }}</div>@endif
                        @if($pengajuan->seminar_tanggal)
                            <div class="small text-muted mt-1">Jadwal tersedia</div>
                        @endif
                    </td>
                    <td class="align-top">
                        <div class="student-action-buttons">
                        @if($sudahTerjadwal)
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#jadwal-info-{{ $pengajuan->id }}">Lihat Jadwal</button>
                        @else
                            <a href="{{ route('mahasiswa.seminar.kelayakan.create', $pengajuan->id) }}" class="btn btn-sm btn-outline-primary">Kelayakan</a>
                            @if($check['allowed'])
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#seminar-form-{{ $pengajuan->id }}">Ajukan Seminar</button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#syarat-{{ $pengajuan->id }}">Lihat Syarat</button>
                            @endif
                        @endif
                        @if($pengajuan->status_seminar === 'menunggu_jadwal')
                            <form action="{{ route('mahasiswa.seminar.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan seminar ini?')">@csrf<button class="btn btn-sm btn-outline-danger">Batalkan</button></form>
                        @endif
                        </div>
                    </td>
                </tr>
                <tr class="collapse" id="jadwal-info-{{ $pengajuan->id }}"><td colspan="4">
                    <div class="row g-3">
                        <div class="col-md-3"><div class="text-muted small">Status Seminar</div><div><span class="badge bg-{{ $badge[$statusSeminar] ?? 'secondary' }} student-badge">{{ $statusSeminarLabel }}</span></div></div>
                        <div class="col-md-3"><div class="text-muted small">Tanggal</div><div class="fw-semibold">{{ $pengajuan->seminar_tanggal ?? '-' }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Jam</div><div class="fw-semibold">{{ $pengajuan->seminar_jam ?? '-' }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Ruangan</div><div class="fw-semibold">{{ $pengajuan->seminar_ruangan ?? '-' }}</div></div>
                    </div>
                </td></tr>
                <tr class="collapse" id="syarat-{{ $pengajuan->id }}"><td colspan="4"><div class="alert alert-warning mb-0"><strong>Belum bisa mengajukan seminar:</strong><ul class="mb-0">@foreach($check['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></div></td></tr>
                <tr class="collapse" id="seminar-form-{{ $pengajuan->id }}"><td colspan="4">
                    <form action="{{ route('mahasiswa.seminar.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">
                        <div class="col-md-6"><label class="form-label">Judul Seminar/Judul Laporan</label><input type="text" name="judul_laporan" class="form-control" value="{{ old('judul_laporan', $pengajuan->judul_laporan) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Laporan Seminar (PDF)</label><input type="file" name="laporan_seminar" class="form-control" accept=".pdf" required></div>
                        <div class="col-12"><label class="form-label">Catatan Tambahan</label><textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea></div>
                        <div class="col-12 text-end"><button type="submit" class="btn btn-primary">Kirim Pengajuan Seminar</button></div>
                    </form>
                </td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada pengajuan SK Magang berjalan yang bisa diproses seminar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
