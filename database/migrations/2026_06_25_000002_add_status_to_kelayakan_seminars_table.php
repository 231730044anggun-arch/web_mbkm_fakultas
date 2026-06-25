<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kelayakan_seminars')) {
            return;
        }

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kelayakan_seminars', 'status')) {
                $table->string('status', 40)->default('menunggu_persetujuan')->after('catatan_mahasiswa');
            }
        });

        if (Schema::hasColumn('kelayakan_seminars', 'status')) {
            DB::table('kelayakan_seminars')
                ->whereNull('status')
                ->update(['status' => 'menunggu_persetujuan']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('kelayakan_seminars') || !Schema::hasColumn('kelayakan_seminars', 'status')) {
            return;
        }

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
