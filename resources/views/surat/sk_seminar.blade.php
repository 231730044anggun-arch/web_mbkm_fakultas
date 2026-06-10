<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Seminar</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    </style>
</head>
<body>
    <h3>Surat Undangan Seminar / Surat Keterangan Seminar</h3>
    <p>Nama: {{ $pengajuan->mahasiswa->nama_lengkap }}</p>
    <p>NIM: {{ $pengajuan->mahasiswa->nim }}</p>
    <p>Judul Laporan: {{ $pengajuan->judul_laporan }}</p>
    <p>Tanggal: {{ optional($pengajuan->seminar_tanggal)->format('d M Y') ?? $pengajuan->seminar_tanggal }}</p>
    <p>Jam: {{ $pengajuan->seminar_jam }}</p>
    <p>Ruangan: {{ $pengajuan->seminar_ruangan }}</p>
</body>
</html>
