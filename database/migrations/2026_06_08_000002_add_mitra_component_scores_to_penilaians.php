<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            if (!Schema::hasColumn('penilaians', 'nilai_absensi')) {
                $table->float('nilai_absensi')->nullable()->after('nilai_lapangan');
            }

            if (!Schema::hasColumn('penilaians', 'nilai_sikap_etika')) {
                $table->float('nilai_sikap_etika')->nullable()->after('nilai_absensi');
            }

            if (!Schema::hasColumn('penilaians', 'nilai_teamwork')) {
                $table->float('nilai_teamwork')->nullable()->after('nilai_sikap_etika');
            }

            if (!Schema::hasColumn('penilaians', 'nilai_disiplin_tanggung_jawab')) {
                $table->float('nilai_disiplin_tanggung_jawab')->nullable()->after('nilai_teamwork');
            }

            if (!Schema::hasColumn('penilaians', 'catatan_mitra')) {
                $table->text('catatan_mitra')->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach (['catatan_mitra', 'nilai_disiplin_tanggung_jawab', 'nilai_teamwork', 'nilai_sikap_etika', 'nilai_absensi'] as $column) {
                if (Schema::hasColumn('penilaians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
