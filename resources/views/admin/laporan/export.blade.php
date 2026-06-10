@extends('layouts.app')
@section('title','Export Laporan')
@section('page-title','Export Laporan')

@section('content')
<div class="card p-4">
    <h6>Export Laporan ({{ $pengajuans->count() }})</h6>
    <table class="table table-bordered table-sm">
        <thead><tr><th>ID</th><th>Mahasiswa</th><th>NIM</th><th>Mitra</th><th>Kota</th><th>Periode</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($pengajuans as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->mahasiswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $p->mahasiswa->nim ?? '' }}</td>
                <td>{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual }}</td>
                <td>{{ $p->mitra->kota ?? $p->mahasiswa->kota ?? '-' }}</td>
                <td>{{ $p->periode->nama_periode ?? '-' }}</td>
                <td>{{ $p->status_pengajuan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
