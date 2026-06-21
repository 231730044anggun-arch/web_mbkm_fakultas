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
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach ($this->decimalColumns() as $column) {
                if (Schema::hasColumn('penilaians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function decimalColumns(): array
    {
        $base = [
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
        ];

        $columns = [];
        foreach (['dosen', 'pembimbing'] as $role) {
            foreach ($base as $column) {
                $columns[] = "{$column}_{$role}";
            }
        }

        return array_merge($columns, [
            'nilai_laporan_dosen',
            'nilai_presentasi_dosen',
            'nilai_seminar_dosen',
            'nilai_laporan_pembimbing',
            'nilai_presentasi_pembimbing',
            'nilai_seminar_pembimbing',
        ]);
    }
};
