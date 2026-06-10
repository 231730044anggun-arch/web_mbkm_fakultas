@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Notifikasi</h6>
        <div class="d-flex gap-2">
            <form action="{{ route('notifikasi.read-all') }}" method="POST">
                @csrf
                <button class="btn btn-primary btn-sm">Tandai Semua Dibaca</button>
            </form>
            <form action="{{ route('notifikasi.destroy-all') }}" method="POST" onsubmit="return confirm('Hapus semua notifikasi Anda?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Hapus Semua</button>
            </form>
        </div>
    </div>

    @forelse($notifikasis as $notifikasi)
        <div class="border-bottom py-3">
            <div class="d-flex justify-content-between gap-3">
                <a href="{{ route('notifikasi.read', $notifikasi->id) }}" class="text-decoration-none text-dark flex-grow-1">
                    <div class="fw-bold">{{ $notifikasi->judul }}</div>
                    <div class="text-muted small">{{ $notifikasi->pesan }}</div>
                    <div class="text-muted small mt-1">{{ $notifikasi->created_at?->format('d M Y H:i') }}</div>
                </a>
                <div class="text-end">
                    <span class="badge bg-{{ $notifikasi->status === 'belum' ? 'warning' : 'secondary' }} mb-2">
                        {{ $notifikasi->status }}
                    </span>
                    <form action="{{ route('notifikasi.destroy', $notifikasi->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">Belum ada notifikasi.</div>
    @endforelse

    <div class="mt-3">{{ $notifikasis->links() }}</div>
</div>
@endsection