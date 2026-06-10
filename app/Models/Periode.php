<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $fillable = ['nama_periode', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'status'];

    public function pengajuans() { return $this->hasMany(PengajuanMagang::class); }
}