<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            if (!Schema::hasColumn('penilaians', 'nilai_logbook')) {
                $table->float('nilai_logbook')->nullable()->after('nilai_dosen');
            }

            if (!Schema::hasColumn('penilaians', 'nilai_presentasi')) {
                $table->float('nilai_presentasi')->nullable()->after('nilai_logbook');
            }

            if (!Schema::hasColumn('penilaians', 'catatan_dosen')) {
                $table->text('catatan_dosen')->nullable()->after('catatan_mitra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table) {
            foreach (['catatan_dosen', 'nilai_presentasi', 'nilai_logbook'] as $column) {
                if (Schema::hasColumn('penilaians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
