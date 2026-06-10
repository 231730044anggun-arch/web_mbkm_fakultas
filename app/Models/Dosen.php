<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $fillable = ['user_id', 'nidn', 'nama_dosen', 'prodi_id', 'no_hp', 'email_dosen', 'status_dosen', 'profile_status'];

    public function user() { return $this->belongsTo(User::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
    public function bimbingans() { return $this->hasMany(Bimbingan::class); }

    public function profileComplete(): bool
    {
        return filled($this->nidn)
            && filled($this->nama_dosen)
            && filled($this->prodi_id)
            && filled($this->no_hp)
            && filled($this->email_dosen)
            && filled($this->status_dosen);
    }

    public function syncProfileStatus(): void
    {
        $this->profile_status = $this->profileComplete() ? 'lengkap' : 'belum_lengkap';
    }
}