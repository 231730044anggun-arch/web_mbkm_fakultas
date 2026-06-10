<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function mahasiswaProfile() { return $this->hasOne(MahasiswaProfile::class); }
    public function dosen() { return $this->hasOne(Dosen::class); }
    public function mitraUser() { return $this->hasOne(MitraUser::class); }
    public function pembimbingLapangan() { return $this->hasOne(PembimbingLapangan::class); }
    public function notifikasis() { return $this->hasMany(Notifikasi::class); }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isMahasiswa() { return $this->role === 'mahasiswa'; }
    public function isDosen() { return $this->role === 'dosen'; }
    public function isMitra() { return $this->role === 'mitra'; }
    public function isPembimbingLapangan() { return $this->role === 'pembimbing_lapangan'; }
    public function isSuperadmin() { return $this->role === 'superadmin'; }
}