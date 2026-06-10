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
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
    public function details() { return $this->hasMany(PenilaianDetail::class); }

    public function hasNilaiLapangan(): bool
    {
        return $this->nilai_absensi !== null
            && $this->nilai_sikap_etika !== null
            && $this->nilai_teamwork !== null
            && $this->nilai_disiplin_tanggung_jawab !== null;
    }

    public function hasNilaiAkademik(): bool
    {
        return $this->nilai_logbook !== null && $this->nilai_presentasi !== null;
    }

    public function isComplete(): bool
    {
        return $this->hasNilaiLapangan() && $this->hasNilaiAkademik();
    }

    public function calculateNilaiLapangan(): ?float
    {
        if (!$this->hasNilaiLapangan()) {
            return null;
        }

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
        if (!$this->hasNilaiAkademik()) {
            return null;
        }

        return round(($this->nilai_logbook * 0.10) + ($this->nilai_presentasi * 0.30), 2);
    }

    public function calculateFinalScore(): void
    {
        $this->nilai_lapangan = $this->calculateNilaiLapangan();
        $this->nilai_dosen = $this->calculateNilaiAkademik();
        $this->nilai_seminar = $this->nilai_presentasi;

        if (!$this->isComplete()) {
            $this->nilai_akhir = null;
            $this->grade = null;
            $this->save();
            return;
        }

        $nilaiAkhir = round($this->nilai_lapangan + $this->nilai_dosen, 2);

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
