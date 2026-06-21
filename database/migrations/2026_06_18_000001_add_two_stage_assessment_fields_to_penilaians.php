<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach ($this->decimalColumns() as $column) {
                if (!Schema::hasColumn('penilaians', $column)) {
                    $table->decimal($column, 5, 2)->nullable();
                }
            }

            if (!Schema::hasColumn('penilaians', 'status_nilai')) {
                $table->string('status_nilai', 30)->nullable();
            }

            if (!Schema::hasColumn('penilaians', 'nama_penguji')) {
                $table->string('nama_penguji')->nullable();
            }

            if (!Schema::hasColumn('penilaians', 'catatan_seminar')) {
                $table->text('catatan_seminar')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach (array_merge($this->decimalColumns(), ['status_nilai', 'nama_penguji', 'catatan_seminar']) as $column) {
                if (Schema::hasColumn('penilaians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function decimalColumns(): array
    {
        return [
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
            'nilai_tahap1_dosen',
            'nilai_tahap1_pembimbing',
            'nilai_sementara',
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
            'presentasi_indikator_11',
            'presentasi_indikator_12',
            'presentasi_indikator_13',
            'presentasi_indikator_14',
            'presentasi_indikator_15',
            'presentasi_indikator_16',
            'nilai_laporan',
            'nilai_presentasi',
        ];
    }
};
