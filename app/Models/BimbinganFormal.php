<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimbinganFormal extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'mahasiswa_id',
        'dosen_id',
        'tanggal_bimbingan',
        'topik',
        'catatan_mahasiswa',
        'lampiran',
        'balasan_dosen',
        'status',
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class, 'pengajuan_id'); }
    public function mahasiswa() { return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'dosen_id'); }
}
