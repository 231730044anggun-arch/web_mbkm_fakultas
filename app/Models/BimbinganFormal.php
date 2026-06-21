<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimbinganFormal extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'mahasiswa_id',
        'tujuan_bimbingan',
        'dosen_id',
        'pembimbing_lapangan_id',
        'tanggal_bimbingan',
        'topik',
        'catatan_mahasiswa',
        'lampiran',
        'balasan_dosen',
        'balasan_pembimbing',
        'status',
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class, 'pengajuan_id'); }
    public function mahasiswa() { return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'dosen_id'); }
    public function pembimbingLapangan() { return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id'); }
}
