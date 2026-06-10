@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
@include('partials.alerts')

<div class="card p-4 master-table-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h5 class="mb-1">Daftar User</h5>
            <p class="text-muted mb-0">Kelola akun login dasar dan status akses pengguna.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Tambah User
        </a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="master-table-toolbar d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Tampilkan</span>
            <select name="per_page" class="form-select form-select-sm master-per-page" onchange="this.form.submit()">
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected((int) request('per_page', 10) === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <span class="text-muted small">data</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
            <label for="role-user" class="text-muted small mb-0">Role:</label>
            <select id="role-user" name="role" class="form-select form-select-sm" style="width: 160px" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                @foreach(['mahasiswa','dosen','mitra','pembimbing_lapangan','admin','superadmin'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>

            <label for="search-user" class="text-muted small mb-0">Cari:</label>
            <input id="search-user" type="text" name="search" class="form-control form-control-sm master-search"
                   placeholder="Cari nama, email, role, status..." value="{{ request('search') }}">
            <button class="btn btn-sm btn-outline-primary">Cari</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="master-table-wrap">
        <table class="table table-hover align-middle master-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold text-wrap-cell">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge badge-purple">{{ ucwords(str_replace('_', ' ', $u->role)) }}</span></td>
                        <td><span class="badge bg-{{ $u->status === 'aktif' ? 'success' : 'danger' }}">{{ $u->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="action" value="delete">
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini jika tidak punya data historis? Jika punya data terkait, sistem akan menolak hapus.')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.master.partials.pagination', ['paginator' => $users])
</div>
@endsection