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

    public function availableRoles(): array
    {
        $roles = [$this->role];

        if ($this->relationLoaded('mahasiswaProfile') ? $this->mahasiswaProfile : $this->mahasiswaProfile()->exists()) {
            $roles[] = 'mahasiswa';
        }

        if ($this->relationLoaded('dosen') ? $this->dosen : $this->dosen()->exists()) {
            $roles[] = 'dosen';
        }

        if ($this->relationLoaded('pembimbingLapangan') ? $this->pembimbingLapangan : $this->pembimbingLapangan()->exists()) {
            $roles[] = 'pembimbing_lapangan';
        }

        if ($this->relationLoaded('mitraUser') ? $this->mitraUser : $this->mitraUser()->exists()) {
            $roles[] = 'mitra';
        }

        return array_values(array_unique(array_filter($roles)));
    }

    public function hasRoleAccess(string $role): bool
    {
        return in_array($role, $this->availableRoles(), true);
    }

    public function activeRole(): string
    {
        $activeRole = session('active_role');

        return $activeRole && $this->hasRoleAccess($activeRole) ? $activeRole : $this->role;
    }

    public function roleLabel(?string $role = null): string
    {
        return match ($role ?: $this->activeRole()) {
            'superadmin' => 'Superadmin',
            'admin' => 'Admin',
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen Pembimbing',
            'pembimbing_lapangan' => 'Pembimbing Lapangan',
            'mitra' => 'Mitra',
            default => ucwords(str_replace('_', ' ', (string) ($role ?: $this->activeRole()))),
        };
    }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isMahasiswa() { return $this->role === 'mahasiswa'; }
    public function isDosen() { return $this->role === 'dosen'; }
    public function isMitra() { return $this->role === 'mitra'; }
    public function isPembimbingLapangan() { return $this->role === 'pembimbing_lapangan'; }
    public function isSuperadmin() { return $this->role === 'superadmin'; }
}
