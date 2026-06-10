@extends('layouts.app')
@section('title', 'Daftar Mitra')
@section('page-title', 'Daftar Mitra')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Daftar Mitra</h6>
            <div class="text-muted small">Referensi instansi/mitra yang tercatat di sistem.</div>
        </div>
        <form method="GET" action="{{ route('mahasiswa.mitra.index') }}" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari mitra, kota, bidang...">
            <button class="btn btn-sm btn-outline-primary">Cari</button>
        </form>
    </div>

    <div class="row g-3">
        @forelse($mitras as $mitra)
        <div class="col-md-6 col-xl-4">
            <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <h6 class="fw-bold mb-0">{{ $mitra->nama_instansi }}</h6>
                    <span class="badge bg-{{ $mitra->status_mitra === 'aktif' ? 'success' : 'secondary' }}">{{ ucfirst($mitra->status_mitra ?? '-') }}</span>
                </div>
                <div class="small text-muted mb-2">{{ $mitra->jenis_mitra ?? '-' }} / {{ $mitra->bidang_industri ?? '-' }}</div>
                <div class="small"><i class="bi bi-geo-alt me-1"></i>{{ $mitra->kota ?? '-' }}, {{ $mitra->provinsi ?? '-' }}</div>
                <div class="small"><i class="bi bi-envelope me-1"></i>{{ $mitra->email ?? '-' }}</div>
                <div class="small"><i class="bi bi-telephone me-1"></i>{{ $mitra->no_telp ?? '-' }}</div>
            </div>
        </div>
        @empty
        <div class="col-12"><div class="text-center text-muted py-4">Belum ada data mitra.</div></div>
        @endforelse
    </div>

    <div class="mt-3">{{ $mitras->links() }}</div>
</div>
@endsection

