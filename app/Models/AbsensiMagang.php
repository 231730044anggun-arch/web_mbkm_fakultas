<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiMagang extends Model
{
    protected $fillable = [
        'pengajuan_magang_id',
        'mahasiswa_id',
        'mitra_id',
        'pembimbing_lapangan_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'keterangan',
        'bukti_hadir',
        'status',
        'catatan_mitra',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'validated_at' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'pengajuan_magang_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id');
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function pembimbingLapangan()
    {
        return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
