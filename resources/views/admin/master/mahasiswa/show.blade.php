@extends('layouts.app')
@section('title', 'Detail Mahasiswa')
@section('page-title', 'Detail Mahasiswa')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3"><h6 class="fw-bold">Detail Mahasiswa</h6><a href="{{ route('admin.master.mahasiswa.edit', $mahasiswa) }}" class="btn btn-sm btn-outline-warning">Edit</a></div>
    @php($items = ['NIM'=>$mahasiswa->nim,'Nama Lengkap'=>$mahasiswa->nama_lengkap,'Email'=>$mahasiswa->email ?: $mahasiswa->user?->email,'Akun Login'=>$mahasiswa->user?->email,'Kelas'=>$mahasiswa->kelasMaster?->nama_kelas ?: $mahasiswa->kelas,'Jenis Kelamin'=>$mahasiswa->jenis_kelamin,'Alamat'=>$mahasiswa->alamat_lengkap,'Nomor HP'=>$mahasiswa->no_hp,'Tempat Lahir'=>$mahasiswa->tempat_lahir,'Tanggal Lahir'=>$mahasiswa->tanggal_lahir,'Angkatan'=>$mahasiswa->angkatanMaster?->tahun ?: $mahasiswa->angkatan,'Fakultas'=>$mahasiswa->fakultas?->nama_fakultas,'Program Studi'=>$mahasiswa->prodi?->nama_prodi,'Semester'=>$mahasiswa->semester,'SKS Lulus'=>$mahasiswa->sks_lulus,'IPK'=>$mahasiswa->ipk,'Status Mahasiswa'=>$mahasiswa->status_mahasiswa,'Status Profile'=>str_replace('_',' ', $mahasiswa->profile_status)])
    <div class="row">@foreach($items as $label=>$value)<div class="col-md-6 mb-3"><div class="text-muted small">{{ $label }}</div><div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div></div>@endforeach</div>
    <a href="{{ route('admin.master.mahasiswa.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection