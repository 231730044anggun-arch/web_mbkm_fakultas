<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    protected $fillable = ['nama_fakultas'];

    public function prodis() { return $this->hasMany(Prodi::class); }
    public function mahasiswaProfiles() { return $this->hasMany(MahasiswaProfile::class); }
}