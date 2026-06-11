@extends('layouts.app')
@section('title', 'Seminar Magang')
@section('page-title', 'Seminar Magang')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Data belum bisa disimpan.</strong><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4">
    <h6 class="fw-bold mb-3">Kelayakan dan Pengajuan Seminar</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Magang</th><th>Kelayakan Seminar</th><th>Seminar</th><th>Jadwal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($pengajuans as $pengajuan)
                @php
                    $kelayakan = $pengajuan->kelayakanSeminar;
                    $check = $eligibility[$pengajuan->id] ?? ['allowed' => false, 'reasons' => []];
                    $badge = ['menunggu_jadwal'=>'warning','terjadwal'=>'success','selesai'=>'primary','ditunda'=>'secondary','dibatalkan'=>'dark','belum'=>'secondary'];
                    $approvalBadge = ['menunggu'=>'warning','disetujui'=>'success','revisi'=>'danger','ditolak'=>'danger'];
                @endphp
                <tr>
                    <td>
                        <strong>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</strong>
                        <div class="small text-muted">{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</div>
                        <div class="small">Pembimbing: {{ $pengajuan->pembimbingLapangan->nama ?? $pengajuan->pic_nama ?? '-' }}</div>
                    </td>
                    <td>
                        @if($kelayakan)
                            <div>Dosen: <span class="badge bg-{{ $approvalBadge[$kelayakan->status_persetujuan_dosen] ?? 'secondary' }}">{{ $kelayakan->status_persetujuan_dosen }}</span></div>
                            <div>Pembimbing: <span class="badge bg-{{ $approvalBadge[$kelayakan->status_persetujuan_pembimbing] ?? 'secondary' }}">{{ $kelayakan->status_persetujuan_pembimbing }}</span></div>
                            @if($kelayakan->catatan_dosen)<div class="small text-danger">Catatan dosen: {{ $kelayakan->catatan_dosen }}</div>@endif
                            @if($kelayakan->catatan_pembimbing)<div class="small text-danger">Catatan pembimbing: {{ $kelayakan->catatan_pembimbing }}</div>@endif
                        @else
                            <span class="text-muted">Belum dikirim</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $badge[$pengajuan->status_seminar ?? 'belum'] ?? 'secondary' }}">{{ $pengajuan->status_seminar ?? 'belum' }}</span>
                        @if($pengajuan->judul_laporan)<div class="small">{{ $pengajuan->judul_laporan }}</div>@endif
                    </td>
                    <td>
                        @if($pengajuan->seminar_tanggal)
                            {{ $pengajuan->seminar_tanggal }}<br>{{ $pengajuan->seminar_jam ?? '-' }}<br>{{ $pengajuan->seminar_ruangan ?? '-' }}
                        @else
                            <span class="text-muted">Belum dijadwalkan</span>
                        @endif
                    </td>
                    <td class="d-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#kelayakan-{{ $pengajuan->id }}">Kelayakan</button>
                        @if($check['allowed'])
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#seminar-form-{{ $pengajuan->id }}">Ajukan Seminar</button>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#syarat-{{ $pengajuan->id }}">Lihat Syarat</button>
                        @endif
                        @if($pengajuan->status_seminar === 'menunggu_jadwal')
                            <form action="{{ route('mahasiswa.seminar.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan seminar ini?')">@csrf<button class="btn btn-sm btn-outline-danger">Batalkan</button></form>
                        @endif
                    </td>
                </tr>
                <tr class="collapse" id="kelayakan-{{ $pengajuan->id }}"><td colspan="5">
                    <form action="{{ route('mahasiswa.seminar.kelayakan.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6"><label class="form-label">Laporan Hasil Magang (PDF)</label><input type="file" name="laporan_hasil_magang" class="form-control" accept=".pdf" required></div>
                        <div class="col-md-6"><label class="form-label">Produk Magang (opsional)</label><input type="file" name="produk_magang" class="form-control" accept=".pdf,.zip,.rar,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"></div>
                        <div class="col-12"><label class="form-label">Uraian Output Magang</label><textarea name="output_magang" class="form-control" rows="4" required placeholder="Jelaskan output/hasil magang yang telah dibuat, misalnya sistem, modul, laporan analisis, desain UI, dokumentasi, SOP, konten, dataset, alat bantu kerja, atau hasil pekerjaan lain selama magang.">{{ old('output_magang', $kelayakan->output_magang ?? '') }}</textarea></div>
                        <div class="col-12"><label class="form-label">Catatan Tambahan</label><textarea name="catatan_mahasiswa" class="form-control" rows="2">{{ old('catatan_mahasiswa', $kelayakan->catatan_mahasiswa ?? '') }}</textarea></div>
                        @if($kelayakan)
                            <div class="col-12">
                                <a href="{{ route('mahasiswa.seminar.kelayakan.file', [$kelayakan->id, 'laporan']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Laporan</a>
                                @if($kelayakan->produk_magang)<a href="{{ route('mahasiswa.seminar.kelayakan.file', [$kelayakan->id, 'produk']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Produk</a>@endif
                            </div>
                        @endif
                        <div class="col-12 text-end"><button class="btn btn-primary">Kirim/Ulang Kelayakan</button></div>
                    </form>
                </td></tr>
                <tr class="collapse" id="syarat-{{ $pengajuan->id }}"><td colspan="5"><div class="alert alert-warning mb-0"><strong>Belum bisa mengajukan seminar:</strong><ul class="mb-0">@foreach($check['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></div></td></tr>
                <tr class="collapse" id="seminar-form-{{ $pengajuan->id }}"><td colspan="5">
                    <form action="{{ route('mahasiswa.seminar.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">
                        <div class="col-md-6"><label class="form-label">Judul Seminar/Judul Laporan</label><input type="text" name="judul_laporan" class="form-control" value="{{ old('judul_laporan', $pengajuan->judul_laporan) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Laporan Seminar (PDF)</label><input type="file" name="laporan_seminar" class="form-control" accept=".pdf" required></div>
                        <div class="col-12"><label class="form-label">Catatan Tambahan</label><textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea></div>
                        <div class="col-12 text-end"><button class="btn btn-primary">Kirim Pengajuan Seminar</button></div>
                    </form>
                </td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada pengajuan SK Magang berjalan yang bisa diproses seminar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection