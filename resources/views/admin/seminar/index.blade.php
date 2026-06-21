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
            <thead class="table-light"><tr><th class="align-top">Mahasiswa</th><th class="align-top">Instansi</th><th class="align-top">Kelayakan</th><th class="align-top">Judul</th><th class="align-top">Status</th><th class="align-top">Jadwal</th><th class="align-top">Aksi</th></tr></thead>
            <tbody>
            @forelse($pengajuans as $pengajuan)
                @php
                    $badge = ['menunggu_jadwal'=>'warning','terjadwal'=>'success','selesai'=>'primary','ditunda'=>'secondary','dibatalkan'=>'dark'];
                    $kelayakanApproved = $pengajuan->kelayakanSeminar?->isApproved();
                    $kelayakanTerlambat = $pengajuan->kelayakanSeminar?->melewatiDeadline() ?? false;
                    $statusSeminar = $pengajuan->status_seminar ?: ($kelayakanApproved ? 'menunggu_jadwal' : 'belum');
                    $formatStatus = fn($status) => ucwords(str_replace('_', ' ', $status ?: '-'));
                    $statusLabel = $statusSeminar === 'menunggu_jadwal' && $kelayakanApproved ? 'Siap Dijadwalkan' : $formatStatus($statusSeminar);
                @endphp
                <tr>
                    <td class="align-top"><strong>{{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</strong><div class="small text-muted">{{ $pengajuan->mahasiswa->nim ?? '-' }}</div></td>
                    <td class="align-top">{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td>
                    <td class="align-top">
                        Dosen Pembimbing: {{ $formatStatus($pengajuan->kelayakanSeminar->status_persetujuan_dosen ?? '-') }}<br>
                        @if($pengajuan->kelayakanSeminar?->catatan_dosen)<span class="small text-muted">Catatan dosen pembimbing: {{ $pengajuan->kelayakanSeminar->catatan_dosen }}</span><br>@endif
                        Pembimbing Lapangan: {{ $formatStatus($pengajuan->kelayakanSeminar->status_persetujuan_pembimbing ?? '-') }}
                        @if($pengajuan->kelayakanSeminar?->catatan_pembimbing)<br><span class="small text-muted">Catatan pembimbing lapangan: {{ $pengajuan->kelayakanSeminar->catatan_pembimbing }}</span>@endif
                        @if($pengajuan->kelayakanSeminar)
                            @if($kelayakanTerlambat)
                                <div class="mt-1"><span class="badge bg-danger">Terlambat</span></div>
                            @endif
                            <div class="small text-muted mt-1">Output: {{ \Illuminate\Support\Str::limit($pengajuan->kelayakanSeminar->output_magang ?: '-', 80) }}</div>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.seminar.file', [$pengajuan->kelayakanSeminar->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Laporan</a>
                                @if($pengajuan->kelayakanSeminar->produk_magang)
                                    <a href="{{ route('admin.seminar.file', [$pengajuan->kelayakanSeminar->id, 'produk']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Produk</a>
                                @endif
                                @if($pengajuan->kelayakanSeminar->draft_jurnal)
                                    <a href="{{ route('admin.seminar.file', [$pengajuan->kelayakanSeminar->id, 'jurnal']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Draft Jurnal</a>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="align-top">{{ $pengajuan->judul_laporan ?? '-' }}</td>
                    <td class="align-top"><span class="badge bg-{{ $badge[$statusSeminar] ?? 'secondary' }}">{{ $statusLabel }}</span></td>
                    <td class="align-top">{{ $pengajuan->seminar_tanggal ?? '-' }}<br>{{ $pengajuan->seminar_jam ?? '-' }}<br>{{ $pengajuan->seminar_ruangan ?? '-' }}</td>
                    <td class="align-top">
                        @if($kelayakanApproved)
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#jadwal-{{ $pengajuan->id }}">{{ $statusSeminar === 'menunggu_jadwal' ? 'Jadwalkan Seminar' : 'Ubah Jadwal' }}</button>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#jadwal-{{ $pengajuan->id }}">Lihat Info</button>
                        @endif
                    </td>
                </tr>
                <tr class="collapse" id="jadwal-{{ $pengajuan->id }}"><td colspan="7">
                    @unless($kelayakanApproved)
                        <div class="alert alert-warning mb-0">Seminar belum dapat dijadwalkan karena belum disetujui oleh dosen pembimbing dan pembimbing lapangan.</div>
                    @else
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
                    @if($pengajuan->status_seminar === 'selesai')
                    <hr>
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-1">Penilaian Tahap 2 - Seminar Hasil Magang</h6>
                            <div class="text-muted small">Nilai Tahap 2 diinput oleh Dosen Pembimbing dan Pembimbing Lapangan melalui menu Penilaian.</div>
                        </div>
                        <div class="col-md-3"><div class="text-muted small">Nilai Tahap 2 Dosen</div><div class="fw-semibold">{{ $pengajuan->penilaian?->nilai_seminar_dosen !== null ? number_format($pengajuan->penilaian->nilai_seminar_dosen, 2) : '-' }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Nilai Tahap 2 Pembimbing</div><div class="fw-semibold">{{ $pengajuan->penilaian?->nilai_seminar_pembimbing !== null ? number_format($pengajuan->penilaian->nilai_seminar_pembimbing, 2) : '-' }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Nilai Seminar Gabungan</div><div class="fw-semibold">{{ $pengajuan->penilaian?->nilai_seminar !== null ? number_format($pengajuan->penilaian->nilai_seminar, 2) : 'Belum lengkap' }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Status Nilai</div><span class="badge bg-{{ $pengajuan->penilaian?->status_nilai === 'final' ? 'success' : 'warning text-dark' }}">{{ $pengajuan->penilaian?->statusNilaiLabel() ?? 'Belum Lengkap' }}</span></div>
                    </div>
                    @elseif($pengajuan->seminar_tanggal)
                    <hr>
                    <div class="alert alert-warning mb-0">Nilai seminar hasil magang dapat diinput setelah admin menyelesaikan status seminar.</div>
                    @endif
                    @endunless
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
