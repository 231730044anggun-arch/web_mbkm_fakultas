<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'status'];

    public function mahasiswaProfiles()
    {
        return $this->hasMany(MahasiswaProfile::class, 'kelas_id');
    }
}