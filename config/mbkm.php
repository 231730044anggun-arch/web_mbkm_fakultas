<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aturan Khusus MBKM per Angkatan
    |--------------------------------------------------------------------------
    |
    | Angkatan di bawah ini memakai alur khusus karena sudah berada pada masa
    | magang/akhir magang. Angkatan lain tetap memakai alur normal.
    |
    */
    'angkatan_khusus_sk_kolektif' => array_filter(array_map('trim', explode(',', env('MBKM_ANGKATAN_KHUSUS_SK_KOLEKTIF', '2023')))),
    'absensi_nonaktif_angkatan' => array_filter(array_map('trim', explode(',', env('MBKM_ABSENSI_NONAKTIF_ANGKATAN', '2023')))),
    'deadline_laporan_magang_angkatan' => [
        '2023' => env('MBKM_DEADLINE_LAPORAN_2023', '2026-06-22 23:59:00'),
    ],
    'periode_magang_angkatan' => [
        '2023' => [
            'tanggal_mulai' => env('MBKM_2023_TANGGAL_MULAI', '2026-02-11'),
            'tanggal_selesai' => env('MBKM_2023_TANGGAL_SELESAI', '2026-07-13'),
            'deadline_administrasi' => env('MBKM_2023_DEADLINE_ADMINISTRASI', '2026-06-22 23:59:00'),
            'estimasi_mulai_seminar' => env('MBKM_2023_ESTIMASI_MULAI_SEMINAR', '2026-07-15'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Saklar absensi umum
    |--------------------------------------------------------------------------
    |
    | Default aktif untuk alur normal. Angkatan khusus tetap bisa dinonaktifkan
    | lewat absensi_nonaktif_angkatan di atas.
    |
    */
    'absensi_aktif' => env('MBKM_ABSENSI_AKTIF', true),
];
