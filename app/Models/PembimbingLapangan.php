<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembimbingLapangan extends Model
{
    protected $fillable = [
        'user_id', 'mitra_id', 'pengajuan_id', 'nama', 'jabatan', 'email', 'no_hp',
        'instansi', 'status', 'profile_status', 'catatan'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function mitra() { return $this->belongsTo(Mitra::class); }
    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }

    public function profileComplete(): bool
    {
        return filled($this->nama)
            && filled($this->email)
            && filled($this->no_hp)
            && filled($this->mitra_id)
            && filled($this->status);
    }

    public function syncProfileStatus(): void
    {
        $this->profile_status = $this->profileComplete() ? 'lengkap' : 'belum_lengkap';
    }
}