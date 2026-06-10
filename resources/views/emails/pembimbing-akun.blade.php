<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informasi Akun Pembimbing Lapangan</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Halo Bapak/Ibu {{ $pembimbing->nama }},</p>

    <p>Akun Pembimbing Lapangan Bapak/Ibu telah dibuat pada Sistem Informasi Magang Fakultas Sains dan Teknologi UIN Sultan Maulana Hasanuddin Banten.</p>

    <p>Akun ini digunakan untuk mendampingi mahasiswa magang, melihat data mahasiswa bimbingan, memantau logbook/aktivitas magang, memvalidasi absensi atau kegiatan mahasiswa, serta memberikan penilaian lapangan sesuai alur magang yang berlaku.</p>

    <p><strong>Berikut informasi mahasiswa yang dibimbing:</strong></p>
    <ul>
        <li>Nama Mahasiswa: {{ $pengajuan->mahasiswa->nama_lengkap ?? '-' }}</li>
        <li>NIM: {{ $pengajuan->mahasiswa->nim ?? '-' }}</li>
        <li>Program Studi: {{ $pengajuan->mahasiswa->prodi->nama_prodi ?? '-' }}</li>
        <li>Tempat Magang: {{ $pengajuan->mitra->nama_instansi ?? $pembimbing->instansi ?? '-' }}</li>
    </ul>

    <p><strong>Berikut informasi akun Bapak/Ibu:</strong></p>
    <ul>
        <li>Email/Username: {{ $pembimbing->email }}</li>
        <li>Password Sementara: {{ $temporaryPassword }}</li>
    </ul>

    <p>Silakan login melalui link berikut:</p>
    <p><a href="{{ url('/login') }}">{{ url('/login') }}</a></p>

    <p>Demi keamanan, mohon Bapak/Ibu mengubah password setelah berhasil login pertama kali.</p>

    <p>Jika terdapat kendala saat login atau data mahasiswa belum sesuai, silakan menghubungi admin/fakultas.</p>

    <p>Terima kasih.</p>
    <p>Admin Sistem Informasi Magang<br>Fakultas Sains dan Teknologi<br>UIN Sultan Maulana Hasanuddin Banten</p>
</body>
</html>