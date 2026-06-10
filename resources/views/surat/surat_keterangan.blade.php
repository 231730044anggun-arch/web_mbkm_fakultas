<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Magang</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Surat Keterangan Magang</h3>
        <p>Universitas / Fakultas</p>
    </div>
    <div class="content">
        <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
        <table>
            <tr><td>Nama</td><td>: {{ $pengajuan->mahasiswa->nama_lengkap }}</td></tr>
            <tr><td>NIM</td><td>: {{ $pengajuan->mahasiswa->nim }}</td></tr>
            <tr><td>Program Studi</td><td>: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td></tr>
            <tr><td>Instansi</td><td>: {{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual }}</td></tr>
            <tr><td>Posisi</td><td>: {{ $pengajuan->posisi_magang }}</td></tr>
            <tr><td>Periode</td><td>: {{ $pengajuan->periode->nama_periode ?? '-' }}</td></tr>
        </table>

        <p>Telah melakukan kegiatan magang sebagaimana dimaksud di atas dengan status: <strong>{{ $pengajuan->status_pengajuan }}</strong>.</p>

        <p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>
</body>
</html>
