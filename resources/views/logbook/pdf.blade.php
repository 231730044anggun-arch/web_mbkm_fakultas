<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Logbook Magang - {{ $pengajuan->mahasiswa->nama_lengkap ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 10px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding:6px; }
        .muted { color:#666; font-size:11px }
        .warning { color: #b45f06; font-weight: bold }
    </style>
</head>
<body>
    <div class="header">
        <h3>Logbook Magang</h3>
        <div class="muted">Periode: {{ $pengajuan->periode->nama_periode ?? '-' }} | Status: {{ $pengajuan->status_pengajuan }}</div>
    </div>

    <table>
        <tr><td width="25%"><strong>Mahasiswa</strong></td><td>{{ $pengajuan->mahasiswa->nama_lengkap ?? '' }} ({{ $pengajuan->mahasiswa->nim ?? '' }})</td></tr>
        <tr><td><strong>Prodi / Fakultas</strong></td><td>{{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '' }} / {{ $pengajuan->mahasiswa->fakultas->nama_fakultas ?? '' }}</td></tr>
        <tr><td><strong>Mitra / Instansi</strong></td><td>{{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual }}</td></tr>
        <tr><td><strong>Pembimbing</strong></td><td>
            @if($pengajuan->bimbingans->count())
                {{ $pengajuan->bimbingans->first()->dosen->nama_dosen ?? '-' }}
            @else - @endif
        </td></tr>
        <tr><td><strong>Periode Magang</strong></td><td>{{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</td></tr>
    </table>

    <h4 style="margin-top:12px">Daftar Logbook</h4>
    @if(count($missing))
        <div class="warning">Logbook mingguan belum lengkap. Belum ada entri untuk minggu: {{ implode(', ', $missing) }}</div>
    @endif
    <table style="margin-top:8px">
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Jam</th><th>Kegiatan</th><th>Output</th><th>Kendala/Solusi</th><th>Status Validasi</th><th>Catatan Dosen</th></tr>
        </thead>
        <tbody>
            @forelse($logbooks as $i => $l)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $l->tanggal }}</td>
                <td>{{ $l->jam_mulai }} - {{ $l->jam_selesai }}</td>
                <td>{{ $l->kegiatan }}</td>
                <td>{{ $l->output_kegiatan ?: 'Tidak ada' }}</td>
                <td>
                    Kendala: {{ $l->kendala ?: 'Tidak ada' }}<br>
                    Solusi: {{ $l->solusi ?: 'Tidak ada' }}
                </td>
                <td>{{ $l->status_validasi }}</td>
                <td>{{ $l->catatan_dosen ?: 'Tidak ada' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="muted">Belum ada logbook</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

