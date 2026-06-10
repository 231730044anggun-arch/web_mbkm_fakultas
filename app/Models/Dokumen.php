<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $fillable = ['pengajuan_id', 'jenis_dokumen', 'file_path', 'tanggal_upload', 'status_verifikasi', 'catatan'];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
}