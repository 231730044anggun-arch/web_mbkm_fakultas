<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pengantar Magang</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 24px; }
        .content { margin: 20px; }
        .signature { margin-top: 48px; }
        table td { padding: 2px 8px 2px 0; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Surat Pengantar / Rekomendasi Magang</h3>
        <p>Universitas / Fakultas</p>
    </div>
    <div class="content">
        <p>Dengan ini kami merekomendasikan mahasiswa berikut untuk mengikuti kegiatan magang:</p>
        <table>
            <tr><td>Nama</td><td>: {{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</td></tr>
            <tr><td>NIM</td><td>: {{ $pengajuan->mahasiswa->nim ?? '-' }}</td></tr>
            <tr><td>Program Studi</td><td>: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</td></tr>
            <tr><td>Instansi Tujuan</td><td>: {{ $pengajuan->mitra->nama_instansi ?? $pengajuan->nama_instansi_manual ?? '-' }}</td></tr>
            <tr><td>Posisi/Bidang</td><td>: {{ $pengajuan->posisi_magang ?? '-' }}</td></tr>
            <tr><td>Periode</td><td>: {{ $pengajuan->tanggal_mulai }} s/d {{ $pengajuan->tanggal_selesai }}</td></tr>
        </table>
        <p>Surat ini dibuat sebagai pengantar/rekomendasi dari fakultas kepada instansi tujuan.</p>
        <div class="signature">
            <p>Mengetahui,</p>
            <p>Koordinator Magang</p>
        </div>
    </div>
</body>
</html>
