<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    protected $fillable = ['pengajuan_id', 'dosen_id', 'tanggal_penugasan', 'status'];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
    public function dosen() { return $this->belongsTo(Dosen::class); }
}