<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'tanggal',
        'kegiatan',
        'output_kegiatan',
        'kendala',
        'solusi',
        'jam_mulai',
        'jam_selesai',
        'bukti_foto',
        'status_validasi',
        'status_dosen',
        'catatan_dosen',
        'status_mitra',
        'catatan_mitra',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'pengajuan_id');
    }
}
