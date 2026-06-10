<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    protected $fillable = ['nama_prodi'];

    public function fakultas() { return $this->belongsTo(Fakultas::class); }
    public function mahasiswaProfiles() { return $this->hasMany(MahasiswaProfile::class); }
    public function dosens() { return $this->hasMany(Dosen::class); }
}