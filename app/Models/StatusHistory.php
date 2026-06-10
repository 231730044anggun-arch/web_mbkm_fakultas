<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $fillable = ['pengajuan_id', 'status', 'keterangan', 'updated_by'];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }
}