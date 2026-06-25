<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    public const TAHAP1_CRITERIA = [
        'inisiatif' => 'Inisiatif',
        'disiplin' => 'Disiplin',
        'ketekunan' => 'Ketekunan',
        'berpikir_kritis' => 'Berpikir kritis, kreatif, dan analitis',
        'adaptasi' => 'Kemampuan beradaptasi',
        'komunikasi' => 'Kemampuan komunikasi lisan maupun tulisan',
        'penampilan' => 'Penampilan',
        'teknikal' => 'Kemampuan teknikal',
        'kerjasama_tim' => 'Kemampuan bekerja sama dalam tim',
        'hasil_pekerjaan' => 'Hasil pekerjaan atau kontribusi',
    ];

    public const LAPORAN_RUBRIK = [
        'laporan_indikator_1' => ['Gambaran umum perusahaan/instansi/lembaga', 5],
        'laporan_indikator_2' => ['Tugas mahasiswa selama PKL/Magang dijelaskan dengan baik dan relevan dengan jurusan', 10],
        'laporan_indikator_3' => ['Pendahuluan/latar belakang dan perumusan masalah ditulis secara jelas', 20],
        'laporan_indikator_4' => ['Masalah dianalisis menggunakan landasan teoritis dan bukti pendukung yang kuat', 25],
        'laporan_indikator_5' => ['Kesimpulan dirumuskan sesuai dengan hasil analisis', 10],
        'laporan_indikator_6' => ['Refleksi diri mencerminkan proses pembelajaran selama PKL/Magang', 10],
        'laporan_indikator_7' => ['Rekomendasi terkait masalah dan institusi jika ada', 5],
        'laporan_indikator_8' => ['Mengikuti panduan laporan PKL/Magang Fakultas Sains dan Teknologi', 5],
        'laporan_indikator_9' => ['Logika penyajian yang runtun', 5],
        'laporan_indikator_10' => ['Bahasa baku serta ilmiah', 5],
    ];

    public const PRESENTASI_RUBRIK = [
        'presentasi_indikator_11' => ['Logika dalam analisis presentasi', 15],
        'presentasi_indikator_12' => ['Substansi isi presentasi', 15],
        'presentasi_indikator_13' => ['Kualitas slides presentasi', 15],
        'presentasi_indikator_14' => ['Sistematika slides presentasi', 15],
        'presentasi_indikator_15' => ['Kemampuan menjelaskan isi laporan PKL/Magang', 15],
        'presentasi_indikator_16' => ['Kemampuan menjawab pertanyaan penguji', 25],
    ];

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
        'status_nilai',
        'nama_penguji',
        'catatan_seminar',
        'catatan',
        'catatan_mitra',
        'catatan_dosen',
        'file_penilaian_formal_dosen',
        'file_penilaian_formal_pembimbing',
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
        'nilai_tahap1_dosen',
        'nilai_tahap1_pembimbing',
        'nilai_sementara',
        'nilai_laporan',
        ...self::TAHAP1_DOSEN_FIELDS,
        ...self::TAHAP1_PEMBIMBING_FIELDS,
        ...self::LAPORAN_FIELDS,
        ...self::PRESENTASI_FIELDS,
        ...self::LAPORAN_DOSEN_FIELDS,
        ...self::PRESENTASI_DOSEN_FIELDS,
        ...self::LAPORAN_PEMBIMBING_FIELDS,
        ...self::PRESENTASI_PEMBIMBING_FIELDS,
        'nilai_laporan_dosen',
        'nilai_presentasi_dosen',
        'nilai_seminar_dosen',
        'nilai_laporan_pembimbing',
        'nilai_presentasi_pembimbing',
        'nilai_seminar_pembimbing',
    ];

    private const TAHAP1_DOSEN_FIELDS = [
        'inisiatif_dosen',
        'disiplin_dosen',
        'ketekunan_dosen',
        'berpikir_kritis_dosen',
        'adaptasi_dosen',
        'komunikasi_dosen',
        'penampilan_dosen',
        'teknikal_dosen',
        'kerjasama_tim_dosen',
        'hasil_pekerjaan_dosen',
    ];

    private const TAHAP1_PEMBIMBING_FIELDS = [
        'inisiatif_pembimbing',
        'disiplin_pembimbing',
        'ketekunan_pembimbing',
        'berpikir_kritis_pembimbing',
        'adaptasi_pembimbing',
        'komunikasi_pembimbing',
        'penampilan_pembimbing',
        'teknikal_pembimbing',
        'kerjasama_tim_pembimbing',
        'hasil_pekerjaan_pembimbing',
    ];

    private const LAPORAN_FIELDS = [
        'laporan_indikator_1',
        'laporan_indikator_2',
        'laporan_indikator_3',
        'laporan_indikator_4',
        'laporan_indikator_5',
        'laporan_indikator_6',
        'laporan_indikator_7',
        'laporan_indikator_8',
        'laporan_indikator_9',
        'laporan_indikator_10',
    ];

    private const PRESENTASI_FIELDS = [
        'presentasi_indikator_11',
        'presentasi_indikator_12',
        'presentasi_indikator_13',
        'presentasi_indikator_14',
        'presentasi_indikator_15',
        'presentasi_indikator_16',
    ];

    private const LAPORAN_DOSEN_FIELDS = [
        'laporan_indikator_1_dosen',
        'laporan_indikator_2_dosen',
        'laporan_indikator_3_dosen',
        'laporan_indikator_4_dosen',
        'laporan_indikator_5_dosen',
        'laporan_indikator_6_dosen',
        'laporan_indikator_7_dosen',
        'laporan_indikator_8_dosen',
        'laporan_indikator_9_dosen',
        'laporan_indikator_10_dosen',
    ];

    private const PRESENTASI_DOSEN_FIELDS = [
        'presentasi_indikator_11_dosen',
        'presentasi_indikator_12_dosen',
        'presentasi_indikator_13_dosen',
        'presentasi_indikator_14_dosen',
        'presentasi_indikator_15_dosen',
        'presentasi_indikator_16_dosen',
    ];

    private const LAPORAN_PEMBIMBING_FIELDS = [
        'laporan_indikator_1_pembimbing',
        'laporan_indikator_2_pembimbing',
        'laporan_indikator_3_pembimbing',
        'laporan_indikator_4_pembimbing',
        'laporan_indikator_5_pembimbing',
        'laporan_indikator_6_pembimbing',
        'laporan_indikator_7_pembimbing',
        'laporan_indikator_8_pembimbing',
        'laporan_indikator_9_pembimbing',
        'laporan_indikator_10_pembimbing',
    ];

    private const PRESENTASI_PEMBIMBING_FIELDS = [
        'presentasi_indikator_11_pembimbing',
        'presentasi_indikator_12_pembimbing',
        'presentasi_indikator_13_pembimbing',
        'presentasi_indikator_14_pembimbing',
        'presentasi_indikator_15_pembimbing',
        'presentasi_indikator_16_pembimbing',
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class); }
    public function details() { return $this->hasMany(PenilaianDetail::class); }

    public static function tahap1Fields(string $role): array
    {
        $suffix = $role === 'pembimbing' ? 'pembimbing' : 'dosen';

        return collect(self::TAHAP1_CRITERIA)
            ->mapWithKeys(fn($label, $key) => ["{$key}_{$suffix}" => $label])
            ->all();
    }

    public static function laporanRubrik(?string $role = null): array
    {
        return self::rubrikForRole(self::LAPORAN_RUBRIK, $role);
    }

    public static function presentasiRubrik(?string $role = null): array
    {
        return self::rubrikForRole(self::PRESENTASI_RUBRIK, $role);
    }

    public function hasNilaiDosenBaru(): bool
    {
        return $this->hasNilaiDosenTahap1();
    }

    public function hasNilaiDosenDasar(): bool
    {
        return $this->hasNilaiDosenTahap1();
    }

    public function hasNilaiPembimbingBaru(): bool
    {
        return $this->hasNilaiPembimbingTahap1();
    }

    public function hasNilaiPembimbingDasar(): bool
    {
        return $this->hasNilaiPembimbingTahap1();
    }

    public function hasNilaiDosenTahap1(): bool
    {
        return $this->allFilled(self::TAHAP1_DOSEN_FIELDS);
    }

    public function hasNilaiPembimbingTahap1(): bool
    {
        return $this->allFilled(self::TAHAP1_PEMBIMBING_FIELDS);
    }

    public function hasNilaiSeminar(): bool
    {
        return $this->hasNilaiSeminarDosen() || $this->hasNilaiSeminarPembimbing();
    }

    public function hasNilaiSeminarLengkap(): bool
    {
        return $this->hasNilaiSeminarDosen() && $this->hasNilaiSeminarPembimbing();
    }

    public function hasNilaiSeminarDosen(): bool
    {
        return $this->nilai_laporan_dosen !== null && $this->nilai_presentasi_dosen !== null;
    }

    public function hasNilaiSeminarPembimbing(): bool
    {
        return $this->nilai_laporan_pembimbing !== null && $this->nilai_presentasi_pembimbing !== null;
    }

    public function hasNilaiSementaraLengkap(): bool
    {
        return $this->hasNilaiDosenTahap1() && $this->hasNilaiPembimbingTahap1();
    }

    public function isComplete(): bool
    {
        return $this->hasNilaiSementaraLengkap() && $this->hasNilaiSeminarLengkap();
    }

    public function calculateNilaiDosenBaru(): ?float
    {
        return $this->calculateAverage(self::TAHAP1_DOSEN_FIELDS);
    }

    public function calculateNilaiDosenSementara(): ?float
    {
        return $this->calculateNilaiDosenBaru();
    }

    public function calculateNilaiPembimbingBaru(): ?float
    {
        return $this->calculateAverage(self::TAHAP1_PEMBIMBING_FIELDS);
    }

    public function calculateNilaiPembimbingSementara(): ?float
    {
        return $this->calculateNilaiPembimbingBaru();
    }

    public function nilaiAkhirSementara(): ?float
    {
        if (!$this->hasNilaiSementaraLengkap()) return null;

        $nilaiDosen = $this->nilai_tahap1_dosen ?? $this->calculateNilaiDosenBaru();
        $nilaiPembimbing = $this->nilai_tahap1_pembimbing ?? $this->calculateNilaiPembimbingBaru();

        if ($nilaiDosen === null || $nilaiPembimbing === null) return null;

        return round(($nilaiDosen * 0.50) + ($nilaiPembimbing * 0.50), 2);
    }

    public function calculateNilaiLaporan(?string $role = null): ?float
    {
        return $this->calculateWeighted(self::laporanRubrik($role));
    }

    public function calculateNilaiPresentasi(?string $role = null): ?float
    {
        return $this->calculateWeighted(self::presentasiRubrik($role));
    }

    public function calculateNilaiSeminar(?string $role = null): ?float
    {
        if ($role === 'dosen') {
            $laporan = $this->nilai_laporan_dosen ?? $this->calculateNilaiLaporan('dosen');
            $presentasi = $this->nilai_presentasi_dosen ?? $this->calculateNilaiPresentasi('dosen');
        } elseif ($role === 'pembimbing') {
            $laporan = $this->nilai_laporan_pembimbing ?? $this->calculateNilaiLaporan('pembimbing');
            $presentasi = $this->nilai_presentasi_pembimbing ?? $this->calculateNilaiPresentasi('pembimbing');
        } else {
            $laporan = $this->nilai_laporan ?? $this->calculateNilaiLaporan();
            $presentasi = $this->nilai_presentasi ?? $this->calculateNilaiPresentasi();
        }

        if ($laporan === null || $presentasi === null) return null;

        return round(($laporan * 0.50) + ($presentasi * 0.50), 2);
    }

    public function calculateFinalScore(): void
    {
        $this->nilai_tahap1_dosen = $this->calculateNilaiDosenBaru();
        $this->nilai_tahap1_pembimbing = $this->calculateNilaiPembimbingBaru();
        $this->nilai_dosen_total = $this->nilai_tahap1_dosen;
        $this->nilai_pembimbing_total = $this->nilai_tahap1_pembimbing;
        $this->nilai_dosen = $this->nilai_tahap1_dosen;
        $this->nilai_lapangan = $this->nilai_tahap1_pembimbing;

        $this->nilai_laporan_dosen = $this->calculateNilaiLaporan('dosen') ?? $this->nilai_laporan_dosen;
        $this->nilai_presentasi_dosen = $this->calculateNilaiPresentasi('dosen') ?? $this->nilai_presentasi_dosen;
        $this->nilai_seminar_dosen = $this->calculateNilaiSeminar('dosen');

        $this->nilai_laporan_pembimbing = $this->calculateNilaiLaporan('pembimbing') ?? $this->nilai_laporan_pembimbing;
        $this->nilai_presentasi_pembimbing = $this->calculateNilaiPresentasi('pembimbing') ?? $this->nilai_presentasi_pembimbing;
        $this->nilai_seminar_pembimbing = $this->calculateNilaiSeminar('pembimbing');

        $hasSeminarDosen = $this->hasNilaiSeminarDosen();
        $hasSeminarPembimbing = $this->hasNilaiSeminarPembimbing();

        if ($hasSeminarDosen && $hasSeminarPembimbing) {
            $this->nilai_laporan = round(($this->nilai_laporan_dosen * 0.50) + ($this->nilai_laporan_pembimbing * 0.50), 2);
            $this->nilai_presentasi = round(($this->nilai_presentasi_dosen * 0.50) + ($this->nilai_presentasi_pembimbing * 0.50), 2);
            $this->nilai_seminar = round(($this->nilai_seminar_dosen * 0.50) + ($this->nilai_seminar_pembimbing * 0.50), 2);
        } elseif ($hasSeminarDosen) {
            $this->nilai_laporan = $this->nilai_laporan_dosen;
            $this->nilai_presentasi = $this->nilai_presentasi_dosen;
            $this->nilai_seminar = $this->nilai_seminar_dosen;
        } elseif ($hasSeminarPembimbing) {
            $this->nilai_laporan = $this->nilai_laporan_pembimbing;
            $this->nilai_presentasi = $this->nilai_presentasi_pembimbing;
            $this->nilai_seminar = $this->nilai_seminar_pembimbing;
        } else {
            $this->nilai_laporan = null;
            $this->nilai_presentasi = null;
            $this->nilai_seminar = null;
        }

        $this->nilai_sementara = $this->nilaiAkhirSementara();

        if (!$this->hasNilaiSementaraLengkap()) {
            $this->status_nilai = 'belum_lengkap';
            $this->nilai_akhir = null;
            $this->grade = null;
            $this->save();
            return;
        }

        if (!$this->hasNilaiSeminar()) {
            $this->status_nilai = 'sementara';
            $this->nilai_akhir = null;
            $this->grade = null;
            $this->save();
            return;
        }

        $nilaiAkhir = round(
            ($this->nilai_tahap1_dosen * 0.40) +
            ($this->nilai_tahap1_pembimbing * 0.40) +
            ($this->nilai_seminar * 0.20),
            2
        );

        $this->status_nilai = $this->hasNilaiSeminarLengkap() ? 'final' : 'akhir_saat_ini';
        $this->nilai_akhir = min($nilaiAkhir, 100);
        $this->grade = self::gradeFor($this->nilai_akhir);
        $this->save();
    }

    public function statusNilaiLabel(): string
    {
        return match ($this->status_nilai) {
            'final' => 'Nilai Akhir',
            'akhir_saat_ini' => 'Nilai Akhir Saat Ini',
            'sementara' => 'Nilai Sementara',
            default => 'Nilai Belum Lengkap',
        };
    }

    public function seminarStatusLabel(): string
    {
        if ($this->hasNilaiSeminarLengkap()) {
            return 'Nilai Seminar Lengkap';
        }

        if ($this->hasNilaiSeminar()) {
            return 'Menunggu Penilai Kedua';
        }

        return 'Belum Diisi';
    }

    public function statusNilaiMessage(): string
    {
        return match ($this->status_nilai) {
            'final' => 'Nilai akhir sudah lengkap.',
            'akhir_saat_ini' => 'Nilai akhir akan diperbarui otomatis setelah penilai kedua mengisi nilai seminar hasil magang.',
            'sementara' => 'Nilai sementara akan diperbarui setelah nilai seminar hasil magang diinput.',
            default => 'Nilai belum tersedia karena penilaian Dosen Pembimbing dan Pembimbing Lapangan belum lengkap.',
        };
    }

    public static function gradeFor(?float $nilai): ?string
    {
        if ($nilai === null) return null;

        return match (true) {
            $nilai >= 80 => 'A',
            $nilai >= 75 => 'B+',
            $nilai >= 70 => 'B',
            $nilai >= 65 => 'C+',
            $nilai >= 60 => 'C',
            $nilai >= 45 => 'D',
            default => 'E',
        };
    }

    private function allFilled(array $fields): bool
    {
        foreach ($fields as $field) {
            if ($this->{$field} === null) return false;
        }

        return true;
    }

    private function calculateAverage(array $fields): ?float
    {
        if (!$this->allFilled($fields)) return null;

        $total = 0;
        foreach ($fields as $field) {
            $total += (float) $this->{$field};
        }

        return round($total / count($fields), 2);
    }

    private function calculateWeighted(array $rubrik): ?float
    {
        $total = 0;
        foreach ($rubrik as $field => [, $weight]) {
            if ($this->{$field} === null) return null;
            $total += (float) $this->{$field} * ($weight / 100);
        }

        return round($total, 2);
    }

    private static function rubrikForRole(array $rubrik, ?string $role): array
    {
        if (!in_array($role, ['dosen', 'pembimbing'], true)) {
            return $rubrik;
        }

        return collect($rubrik)
            ->mapWithKeys(fn($value, $field) => ["{$field}_{$role}" => $value])
            ->all();
    }
}
