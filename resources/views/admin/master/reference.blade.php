@extends('layouts.app')
@section('title', $config['title'])
@section('page-title', $config['title'])

@section('content')
@include('partials.alerts')
<div class="card p-4 mb-3">
    <form action="{{ route('admin.master.reference.store', $type) }}" method="POST" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-4">
            <label class="form-label fw-semibold">{{ $config['title'] }}</label>
            <input type="{{ $type === 'angkatan' ? 'number' : 'text' }}" name="{{ $field }}" class="form-control" required>
        </div>
        @if($type === 'program-studi')
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fakultas</label>
                <select name="fakultas_id" class="form-select">
                    <option value="">-- Pilih Fakultas --</option>
                    @foreach($fakultas as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(in_array($type, ['kelas', 'angkatan'], true))
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        @endif
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Tambah</button>
        </div>
    </form>
</div>
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-bold mb-0">Daftar {{ $config['title'] }}</h6>
        <a href="{{ route('admin.master.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Nama/Data</th>
                @if($type === 'program-studi')<th>Fakultas</th>@endif
                @if(in_array($type, ['kelas', 'angkatan'], true))<th>Status</th>@endif
                <th width="320">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <form action="{{ route('admin.master.reference.update', [$type, $item->id]) }}" method="POST">
                        @csrf @method('PUT')
                        <td><input type="{{ $type === 'angkatan' ? 'number' : 'text' }}" name="{{ $field }}" class="form-control form-control-sm" value="{{ $item->{$field} }}" required></td>
                        @if($type === 'program-studi')
                            <td>
                                <select name="fakultas_id" class="form-select form-select-sm">
                                    <option value="">-</option>
                                    @foreach($fakultas as $f)
                                        <option value="{{ $f->id }}" @selected($item->fakultas_id == $f->id)>{{ $f->nama_fakultas }}</option>
                                    @endforeach
                                </select>
                            </td>
                        @endif
                        @if(in_array($type, ['kelas', 'angkatan'], true))
                            <td>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="aktif" @selected($item->status === 'aktif')>Aktif</option>
                                    <option value="nonaktif" @selected($item->status === 'nonaktif')>Nonaktif</option>
                                </select>
                            </td>
                        @endif
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Simpan</button>
                    </form>
                            <form action="{{ route('admin.master.reference.destroy', [$type, $item->id]) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus/nonaktifkan data ini?')">Hapus</button>
                            </form>
                        </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection