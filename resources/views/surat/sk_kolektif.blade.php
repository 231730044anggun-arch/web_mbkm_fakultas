<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SK Magang Kolektif</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 10px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Surat Keterangan Magang Kolektif</h3>
    </div>
    <table>
        <thead>
            <tr><th>No</th><th>Nama</th><th>NIM</th><th>Prodi</th><th>Instansi</th><th>Posisi</th></tr>
        </thead>
        <tbody>
            @foreach($pengajuans as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->mahasiswa->nama_lengkap }}</td>
                <td>{{ $p->mahasiswa->nim }}</td>
                <td>{{ $p->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td>{{ $p->mitra->nama_instansi ?? $p->nama_instansi_manual }}</td>
                <td>{{ $p->posisi_magang }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
