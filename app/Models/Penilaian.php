<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'nilai_lapangan',
        'nilai_absensi',
        'nilai_sikap_etika',
        'nilai_teamwork',
        'nilai_disiplin_tanggung_jawab',
        'nilai_dosen',
        'nilai_logbook',
        'nilai_presentasi',
        'nilai_akhir',
        'nilai_seminar',
        'grade',
        'catatan',
        'catatan_mitra',
        'catatan_dosen',
        'dosen_kehadiran_disiplin',
        'dosen_kinerja_sikap',
        'dosen_logbook_kegiatan',
        'dosen_luaran',
        'dosen_laporan_akhir',
        'nilai_dosen_total',
        'pembimbing_kehadiran_disiplin',
        'pembimbing_kinerja_sikap',
        'pembimbing_logbook_kegiatan',
        'pembimbing_luaran',
        'pembimbing_laporan_akhir',
        'nilai_pembimbing_total',
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
    public function details() { return $this->hasMany(PenilaianDetail::class); }

    public function hasNilaiDosenBaru(): bool
    {
        return $this->dosen_kehadiran_disiplin !== null
            && $this->dosen_kinerja_sikap !== null
            && $this->dosen_logbook_kegiatan !== null
            && $this->dosen_luaran !== null
            && $this->dosen_laporan_akhir !== null;
    }

    public function hasNilaiPembimbingBaru(): bool
    {
        return $this->pembimbing_kehadiran_disiplin !== null
            && $this->pembimbing_kinerja_sikap !== null
            && $this->pembimbing_logbook_kegiatan !== null
            && $this->pembimbing_luaran !== null
            && $this->pembimbing_laporan_akhir !== null;
    }

    public function hasNilaiLapangan(): bool
    {
        return $this->hasNilaiPembimbingBaru() || (
            $this->nilai_absensi !== null
            && $this->nilai_sikap_etika !== null
            && $this->nilai_teamwork !== null
            && $this->nilai_disiplin_tanggung_jawab !== null
        );
    }

    public function hasNilaiAkademik(): bool
    {
        return $this->hasNilaiDosenBaru() || ($this->nilai_logbook !== null && $this->nilai_presentasi !== null);
    }

    public function isComplete(): bool
    {
        return $this->hasNilaiDosenBaru() && $this->hasNilaiPembimbingBaru();
    }

    public function calculateNilaiDosenBaru(): ?float
    {
        if (!$this->hasNilaiDosenBaru()) return null;

        return round(
            ($this->dosen_kehadiran_disiplin * 0.15) +
            ($this->dosen_kinerja_sikap * 0.30) +
            ($this->dosen_logbook_kegiatan * 0.15) +
            ($this->dosen_luaran * 0.20) +
            ($this->dosen_laporan_akhir * 0.20),
            2
        );
    }

    public function calculateNilaiPembimbingBaru(): ?float
    {
        if (!$this->hasNilaiPembimbingBaru()) return null;

        return round(
            ($this->pembimbing_kehadiran_disiplin * 0.15) +
            ($this->pembimbing_kinerja_sikap * 0.30) +
            ($this->pembimbing_logbook_kegiatan * 0.15) +
            ($this->pembimbing_luaran * 0.20) +
            ($this->pembimbing_laporan_akhir * 0.20),
            2
        );
    }

    public function calculateNilaiLapangan(): ?float
    {
        if ($this->hasNilaiPembimbingBaru()) return $this->calculateNilaiPembimbingBaru();
        if (!$this->hasNilaiLapangan()) return null;

        return round(
            ($this->nilai_absensi * 0.10) +
            ($this->nilai_sikap_etika * 0.15) +
            ($this->nilai_teamwork * 0.15) +
            ($this->nilai_disiplin_tanggung_jawab * 0.20),
            2
        );
    }

    public function calculateNilaiAkademik(): ?float
    {
        if ($this->hasNilaiDosenBaru()) return $this->calculateNilaiDosenBaru();
        if (!$this->hasNilaiAkademik()) return null;

        return round(($this->nilai_logbook * 0.10) + ($this->nilai_presentasi * 0.30), 2);
    }

    public function calculateFinalScore(): void
    {
        $this->nilai_dosen_total = $this->calculateNilaiDosenBaru();
        $this->nilai_pembimbing_total = $this->calculateNilaiPembimbingBaru();
        $this->nilai_dosen = $this->nilai_dosen_total;
        $this->nilai_lapangan = $this->nilai_pembimbing_total;

        if (!$this->isComplete()) {
            $this->nilai_akhir = null;
            $this->grade = null;
            $this->save();
            return;
        }

        $nilaiAkhir = round(($this->nilai_dosen_total * 0.50) + ($this->nilai_pembimbing_total * 0.50), 2);

        $grade = match (true) {
            $nilaiAkhir >= 80 => 'A',
            $nilaiAkhir >= 75 => 'B+',
            $nilaiAkhir >= 70 => 'B',
            $nilaiAkhir >= 65 => 'C+',
            $nilaiAkhir >= 60 => 'C',
            $nilaiAkhir >= 45 => 'D',
            default => 'E',
        };

        $this->nilai_akhir = min($nilaiAkhir, 100);
        $this->grade = $grade;
        $this->save();
    }
}