<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kukertas', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_kukertas', 'tanggal_mulai_kukerta')) {
                $table->date('tanggal_mulai_kukerta')->nullable()->after('target_kukerta');
            }
            if (!Schema::hasColumn('laporan_kukertas', 'tanggal_selesai_kukerta')) {
                $table->date('tanggal_selesai_kukerta')->nullable()->after('tanggal_mulai_kukerta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kukertas', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_kukertas', 'tanggal_selesai_kukerta')) {
                $table->dropColumn('tanggal_selesai_kukerta');
            }
            if (Schema::hasColumn('laporan_kukertas', 'tanggal_mulai_kukerta')) {
                $table->dropColumn('tanggal_mulai_kukerta');
            }
        });
    }
};