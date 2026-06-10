<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianDetail extends Model
{
    protected $fillable = ['penilaian_id', 'aspek', 'nilai'];

    public function penilaian() { return $this->belongsTo(Penilaian::class); }
}