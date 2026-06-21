<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    public function tahunAngkatan(): ?string
    {
        $tahun = $this->angkatanMaster?->tahun ?: $this->angkatan;
        return $tahun !== null && $tahun !== '' ? (string) $tahun : null;
    }

    public function isAngkatanKhususSkKolektif(): bool
    {
        $tahun = $this->tahunAngkatan();
        if (!$tahun) return false;
        return in_array($tahun, array_map('strval', config('mbkm.angkatan_khusus_sk_kolektif', [])), true);
    }

    public function isAbsensiNonaktif(): bool
    {
        $tahun = $this->tahunAngkatan();
        if (!$tahun) return false;
        return in_array($tahun, array_map('strval', config('mbkm.absensi_nonaktif_angkatan', [])), true);
    }

    public function absensiAktif(): bool
    {
        return (bool) config('mbkm.absensi_aktif', true) && !$this->isAbsensiNonaktif();
    }

    public function deadlineLaporanMagang(): ?Carbon
    {
        $tahun = $this->tahunAngkatan();
        $deadline = $tahun ? (config('mbkm.deadline_laporan_magang_angkatan', [])[$tahun] ?? $this->periodeMagangKhusus()['deadline_administrasi'] ?? null) : null;
        return $deadline ? Carbon::parse($deadline)->setTime(23, 59, 0) : null;
    }

    public function deadlineLaporanMagangLabel(): ?string
    {
        return $this->deadlineLaporanMagang()?->locale('id')->translatedFormat('d F Y \p\u\k\u\l H.i');
    }

    public function periodeMagangKhusus(): array
    {
        $tahun = $this->tahunAngkatan();
        return $tahun ? (config('mbkm.periode_magang_angkatan', [])[$tahun] ?? []) : [];
    }

    public function defaultTanggalMulaiMagang(): ?string
    {
        return $this->periodeMagangKhusus()['tanggal_mulai'] ?? null;
    }

    public function defaultTanggalSelesaiMagang(): ?string
    {
        return $this->periodeMagangKhusus()['tanggal_selesai'] ?? null;
    }

    public function hasPenempatanMagangLengkap(?PengajuanMagang $pengajuan = null): bool
    {
        $pengajuan ??= $this->pengajuanMagangAktif();

        return $pengajuan
            && $pengajuan->bimbingans()->exists()
            && filled($pengajuan->pembimbing_lapangan_id)
            && filled($pengajuan->mitra_id)
            && filled($pengajuan->tanggal_mulai)
            && filled($pengajuan->tanggal_selesai);
    }

    public function pengajuanMagangAktif(): ?PengajuanMagang
    {
        return $this->pengajuans()
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest('updated_at')
            ->first();
    }
}
