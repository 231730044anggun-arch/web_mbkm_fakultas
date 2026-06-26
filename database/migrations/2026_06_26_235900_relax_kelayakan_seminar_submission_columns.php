<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelayakan_seminars')) {
            return;
        }

        if (Schema::hasColumn('kelayakan_seminars', 'laporan_hasil_magang')) {
            DB::statement('ALTER TABLE kelayakan_seminars MODIFY laporan_hasil_magang VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('kelayakan_seminars', 'output_magang')) {
            DB::statement('ALTER TABLE kelayakan_seminars MODIFY output_magang TEXT NULL');
        }

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kelayakan_seminars', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('kelayakan_seminars')) {
            return;
        }

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            if (Schema::hasColumn('kelayakan_seminars', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
        });
    }
};
