<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('penilaians', function (Blueprint $table) {
            if (!Schema::hasColumn('penilaians', 'nilai_seminar')) {
                $table->integer('nilai_seminar')->nullable()->after('nilai_akhir');
            }
        });
    }

    public function down(): void {
        Schema::table('penilaians', function (Blueprint $table) {
            $table->dropColumn('nilai_seminar');
        });
    }
};