<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            if (!Schema::hasColumn('dosens', 'email_dosen')) {
                $table->string('email_dosen')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('dosens', 'status_dosen')) {
                $table->string('status_dosen')->default('aktif')->after('email_dosen');
            }
        });

        Schema::table('mitras', function (Blueprint $table) {
            if (!Schema::hasColumn('mitras', 'pembimbing_lapangan_email')) {
                $table->string('pembimbing_lapangan_email')->nullable()->after('pembimbing_lapangan_kontak');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            if (Schema::hasColumn('dosens', 'email_dosen')) {
                $table->dropColumn('email_dosen');
            }
            if (Schema::hasColumn('dosens', 'status_dosen')) {
                $table->dropColumn('status_dosen');
            }
        });

        Schema::table('mitras', function (Blueprint $table) {
            if (Schema::hasColumn('mitras', 'pembimbing_lapangan_email')) {
                $table->dropColumn('pembimbing_lapangan_email');
            }
        });
    }
};
