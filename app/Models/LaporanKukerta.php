<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKukerta extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'mahasiswa_id',
        'lokasi_kukerta',
        'target_kukerta',
        'dokumentasi_kukerta',
        'laporan_kukerta',
        'status',
    ];

    protected $casts = [
        'dokumentasi_kukerta' => 'array',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'pengajuan_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id');
    }
}