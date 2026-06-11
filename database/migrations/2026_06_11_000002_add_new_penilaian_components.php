<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach ([
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
            ] as $column) {
                if (!Schema::hasColumn('penilaians', $column)) {
                    $table->decimal($column, 5, 2)->nullable()->after('catatan_dosen');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach ([
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
            ] as $column) {
                if (Schema::hasColumn('penilaians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};