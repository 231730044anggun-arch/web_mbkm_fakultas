<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angkatan extends Model
{
    protected $fillable = ['tahun', 'status'];

    public function mahasiswaProfiles()
    {
        return $this->hasMany(MahasiswaProfile::class, 'angkatan_id');
    }
}