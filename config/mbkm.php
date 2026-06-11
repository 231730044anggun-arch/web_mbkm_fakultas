<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mode Angkatan Berjalan
    |--------------------------------------------------------------------------
    |
    | Mode ini dipakai untuk angkatan yang sudah menjalani magang, sehingga
    | Surat Pengantar dan Pengajuan SK Magang mahasiswa dilewati. Admin dapat
    | menerbitkan SK Magang kolektif dan absensi dapat dinonaktifkan sementara.
    |
    */
    'mode_angkatan_berjalan' => env('MBKM_MODE_ANGKATAN_BERJALAN', true),
    'absensi_aktif' => env('MBKM_ABSENSI_AKTIF', false),
];