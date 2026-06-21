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
        'tanggal_mulai_kukerta',
        'tanggal_selesai_kukerta',
        'dokumentasi_kukerta',
        'foto_dokumentasi_kukerta',
        'laporan_kukerta',
        'output_kukerta_file',
        'output_kukerta_link',
        'status',
    ];

    protected $casts = [
        'dokumentasi_kukerta' => 'array',
        'foto_dokumentasi_kukerta' => 'array',
        'tanggal_mulai_kukerta' => 'date',
        'tanggal_selesai_kukerta' => 'date',
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
