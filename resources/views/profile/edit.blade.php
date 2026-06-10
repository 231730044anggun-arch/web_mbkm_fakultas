@extends('layouts.app')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
@include('partials.alerts')
<div class="card p-4">
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Akun</label><input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Email Akun</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>

            @if($user->role === 'mahasiswa' && $user->mahasiswaProfile)
                @php($m = $user->mahasiswaProfile)
                <div class="col-md-4"><label class="form-label fw-semibold">Kelas</label><select name="kelas_id" class="form-select"><option value="">-</option>@foreach($kelasOptions as $k)<option value="{{ $k->id }}" @selected(old('kelas_id', $m->kelas_id) == $k->id)>{{ $k->nama_kelas }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="">-</option><option value="Laki-laki" @selected(old('jenis_kelamin', $m->jenis_kelamin) === 'Laki-laki')>Laki-laki</option><option value="Perempuan" @selected(old('jenis_kelamin', $m->jenis_kelamin) === 'Perempuan')>Perempuan</option></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Nomor HP</label><input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $m->no_hp) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email Pribadi</label><input type="email" name="email_pribadi" class="form-control" value="{{ old('email_pribadi', $m->email) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Alamat Lengkap</label><input name="alamat_lengkap" class="form-control" value="{{ old('alamat_lengkap', $m->alamat_lengkap) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Tempat Lahir</label><input name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $m->tempat_lahir) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $m->tanggal_lahir) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Angkatan</label><select name="angkatan_id" class="form-select"><option value="">-</option>@foreach($angkatanOptions as $a)<option value="{{ $a->id }}" @selected(old('angkatan_id', $m->angkatan_id) == $a->id)>{{ $a->tahun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Fakultas</label><select name="fakultas_id" class="form-select"><option value="">-</option>@foreach($fakultas as $f)<option value="{{ $f->id }}" @selected(old('fakultas_id', $m->fakultas_id) == $f->id)>{{ $f->nama_fakultas }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Program Studi</label><select name="prodi_id" class="form-select"><option value="">-</option>@foreach($prodis as $p)<option value="{{ $p->id }}" @selected(old('prodi_id', $m->prodi_id) == $p->id)>{{ $p->nama_prodi }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Semester</label><input type="number" name="semester" class="form-control" value="{{ old('semester', $m->semester) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">SKS Lulus</label><input type="number" name="sks_lulus" class="form-control" value="{{ old('sks_lulus', $m->sks_lulus) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">IPK</label><input type="number" step="0.01" name="ipk" class="form-control" value="{{ old('ipk', $m->ipk) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Status Mahasiswa</label><select name="status_mahasiswa" class="form-select"><option value="aktif" @selected(old('status_mahasiswa', $m->status_mahasiswa) === 'aktif')>Aktif</option><option value="cuti" @selected(old('status_mahasiswa', $m->status_mahasiswa) === 'cuti')>Cuti</option><option value="lulus" @selected(old('status_mahasiswa', $m->status_mahasiswa) === 'lulus')>Lulus</option></select></div>
                <div class="col-md-4"><div class="form-check mt-4"><input type="hidden" name="pernah_cuti" value="0"><input class="form-check-input" type="checkbox" name="pernah_cuti" value="1" id="pernah_cuti" @checked(old('pernah_cuti', $m->pernah_cuti))><label class="form-check-label fw-semibold" for="pernah_cuti">Pernah Cuti</label></div></div>
            @elseif($user->role === 'dosen' && $user->dosen)
                @php($d = $user->dosen)
                <div class="col-md-6"><label class="form-label fw-semibold">Nama Dosen</label><input name="nama_dosen" class="form-control" value="{{ old('nama_dosen', $d->nama_dosen) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">NIDN/NIP</label><input name="nidn" class="form-control" value="{{ old('nidn', $d->nidn) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Program Studi</label><select name="prodi_id" class="form-select"><option value="">-</option>@foreach($prodis as $p)<option value="{{ $p->id }}" @selected(old('prodi_id', $d->prodi_id) == $p->id)>{{ $p->nama_prodi }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Nomor HP</label><input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $d->no_hp) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Email Dosen</label><input type="email" name="email_pribadi" class="form-control" value="{{ old('email_pribadi', $d->email_dosen) }}"></div>
            @elseif($user->role === 'pembimbing_lapangan' && $user->pembimbingLapangan)
                @php($p = $user->pembimbingLapangan)
                <div class="col-md-6"><label class="form-label fw-semibold">Nama Pembimbing</label><input name="nama_pembimbing" class="form-control" value="{{ old('nama_pembimbing', $p->nama) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email Pembimbing</label><input type="email" name="email_pribadi" class="form-control" value="{{ old('email_pribadi', $p->email) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">No HP</label><input name="no_hp" class="form-control" value="{{ old('no_hp', $p->no_hp) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Jabatan</label><input name="jabatan" class="form-control" value="{{ old('jabatan', $p->jabatan) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Mitra/Instansi</label><select name="mitra_id" class="form-select"><option value="">-</option>@foreach($mitras as $m)<option value="{{ $m->id }}" @selected(old('mitra_id', $p->mitra_id) == $m->id)>{{ $m->nama_instansi }}</option>@endforeach</select></div>
            @elseif($user->role === 'mitra' && $user->mitraUser?->mitra)
                <div class="col-md-6"><label class="form-label fw-semibold">Email Instansi</label><input type="email" name="email_pribadi" class="form-control" value="{{ old('email_pribadi', $user->mitraUser->mitra->email) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nomor Telepon</label><input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $user->mitraUser->mitra->no_telp) }}"></div>
            @endif
            <div class="col-12"><button type="submit" class="btn btn-primary px-4">Simpan</button><a href="{{ route('profile.show') }}" class="btn btn-secondary px-4">Kembali</a></div>
        </div>
    </form>
</div>
@endsection