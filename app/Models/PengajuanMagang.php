<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PengajuanMagang extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'pengajuan_awal_id', 'jenis_pengajuan', 'periode_id', 'jenis_magang', 'jenis_mitra', 'mitra_id', 'pembimbing_lapangan_id',
        'nama_instansi_manual', 'alamat_instansi_manual', 'kota_instansi_manual',
        'posisi_magang', 'deskripsi_kegiatan', 'tanggal_mulai', 'tanggal_selesai',
        'durasi', 'status_pengajuan', 'status_surat_pengantar', 'status_surat_keterangan',
        'status_laporan', 'status_seminar', 'nomor_surat_balasan', 'tanggal_surat_balasan',
        'judul_laporan', 'ringkasan_seminar', 'usulan_tanggal_seminar', 'seminar_tanggal',
        'seminar_jam', 'seminar_ruangan', 'catatan_admin', 'catatan_mahasiswa',
        'pic_nama', 'pic_jabatan', 'pic_no_hp', 'pic_email',
    ];

    public function mahasiswa() { return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id'); }
    public function pengajuanAwal() { return $this->belongsTo(PengajuanMagang::class, 'pengajuan_awal_id'); }
    public function pengajuanTurunan() { return $this->hasMany(PengajuanMagang::class, 'pengajuan_awal_id'); }
    public function periode() { return $this->belongsTo(Periode::class); }
    public function mitra() { return $this->belongsTo(Mitra::class); }
    public function dokumens() { return $this->hasMany(Dokumen::class, 'pengajuan_id'); }
    public function bimbingans() { return $this->hasMany(Bimbingan::class, 'pengajuan_id'); }
    public function bimbinganFormals() { return $this->hasMany(BimbinganFormal::class, 'pengajuan_id'); }
    public function dosenPembimbing() { return $this->hasOne(Bimbingan::class, 'pengajuan_id'); }
    public function logbooks() { return $this->hasMany(Logbook::class, 'pengajuan_id'); }
    public function absensis() { return $this->hasMany(AbsensiMagang::class, 'pengajuan_magang_id'); }
    public function penilaian() { return $this->hasOne(Penilaian::class, 'pengajuan_id'); }
    public function kelayakanSeminar() { return $this->hasOne(KelayakanSeminar::class, 'pengajuan_id'); }
    public function statusHistories() { return $this->hasMany(StatusHistory::class, 'pengajuan_id'); }
    public function pembimbingLapangan() { return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id'); }
    public function pembimbingLapanganLegacy() { return $this->hasOne(PembimbingLapangan::class, 'pengajuan_id'); }

    public function hasValidSeminar(): bool
    {
        return in_array($this->status_seminar, ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda'], true);
    }

    public function nilaiFinalLengkap(): bool
    {
        return $this->penilaian && $this->penilaian->isComplete();
    }

    public function isPenempatanKolektif(): bool
    {
        return $this->jenis_pengajuan === 'surat_keterangan'
            && in_array($this->jenis_mitra, ['kolektif', 'penempatan_magang', 'sk_kolektif'], true);
    }

    public function penempatanLengkap(): bool
    {
        $hasTanggal = filled($this->tanggal_mulai) && filled($this->tanggal_selesai);
        if (!$hasTanggal && ($this->mahasiswa?->isAngkatanKhususSkKolektif() ?? false)) {
            $hasTanggal = filled($this->mahasiswa?->defaultTanggalMulaiMagang())
                && filled($this->mahasiswa?->defaultTanggalSelesaiMagang());
        }

        return $this->bimbingans()->exists()
            && filled($this->pembimbing_lapangan_id)
            && filled($this->mitra_id)
            && $hasTanggal;
    }

    public function seminarDateTime(): ?Carbon
    {
        if (blank($this->seminar_tanggal)) {
            return null;
        }

        try {
            return Carbon::parse($this->seminar_tanggal . ' ' . ($this->seminar_jam ?: '00:00'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function seminarSudahBerlangsung(): bool
    {
        return $this->seminarDateTime()?->lessThanOrEqualTo(now()) ?? false;
    }
}
