<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $fillable = [
        'nama_instansi', 'jenis_instansi', 'bidang_industri', 'alamat',
        'kota', 'provinsi', 'email', 'no_telp', 'website',
        'status_mitra', 'status_mitra_detail', 'jenis_mitra', 'nomor_mou',
        'tanggal_mulai_mou', 'tanggal_berakhir_mou', 'file_mou', 'status_mou',
        'pembimbing_lapangan_nama', 'pembimbing_lapangan_jabatan', 'pembimbing_lapangan_kontak',
        'pembimbing_lapangan_email'
    ];

    public function pengajuans() { return $this->hasMany(PengajuanMagang::class); }
    public function absensis() { return $this->hasMany(AbsensiMagang::class, 'mitra_id'); }
    public function mitraUsers() { return $this->hasMany(MitraUser::class); }

    public function getMouStatusLabelAttribute()
    {
        if ($this->status_mou === 'aktif' && $this->tanggal_berakhir_mou && now()->toDateString() > $this->tanggal_berakhir_mou) {
            return 'expired';
        }
        return $this->status_mou;
    }
}

