<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaProfile extends Model
{
    protected $fillable = [
        'user_id', 'nim', 'nama_lengkap', 'kelas', 'kelas_id', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'no_hp', 'email', 'alamat_lengkap', 'kota', 'provinsi', 'kode_pos',
        'prodi_id', 'fakultas_id', 'angkatan', 'angkatan_id', 'ipk', 'semester', 'sks_lulus',
        'pernah_cuti', 'status_mahasiswa', 'profile_status'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
    public function fakultas() { return $this->belongsTo(Fakultas::class); }
    public function kelasMaster() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function angkatanMaster() { return $this->belongsTo(Angkatan::class, 'angkatan_id'); }
    public function pengajuans() { return $this->hasMany(PengajuanMagang::class, 'mahasiswa_id'); }
    public function absensis() { return $this->hasMany(AbsensiMagang::class, 'mahasiswa_id'); }

    public function profileComplete(): bool
    {
        return filled($this->nim)
            && filled($this->nama_lengkap)
            && filled($this->jenis_kelamin)
            && filled($this->tanggal_lahir)
            && filled($this->prodi_id)
            && filled($this->fakultas_id)
            && (filled($this->angkatan_id) || filled($this->angkatan))
            && filled($this->semester)
            && filled($this->sks_lulus)
            && filled($this->ipk)
            && filled($this->status_mahasiswa);
    }

    public function syncProfileStatus(): void
    {
        $this->profile_status = $this->profileComplete() ? 'lengkap' : 'belum_lengkap';
    }

    public function activePengajuanExists($periodeId = null): bool
    {
        $query = $this->pengajuans()
            ->where('jenis_pengajuan', 'surat_pengantar')
            ->whereIn('status_pengajuan', ['pending', 'disetujui', 'berjalan'])
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId));

        return $query->exists();
    }
}