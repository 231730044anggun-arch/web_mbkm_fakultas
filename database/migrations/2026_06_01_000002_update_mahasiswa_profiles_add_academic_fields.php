<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa_profiles', 'semester')) {
                $table->integer('semester')->nullable()->after('angkatan');
            }
            if (!Schema::hasColumn('mahasiswa_profiles', 'sks_lulus')) {
                $table->integer('sks_lulus')->nullable()->after('semester');
            }
            if (!Schema::hasColumn('mahasiswa_profiles', 'pernah_cuti')) {
                $table->boolean('pernah_cuti')->default(false)->after('sks_lulus');
            }
        });
    }

    public function down(): void {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            $table->dropColumn(['semester', 'sks_lulus', 'pernah_cuti']);
        });
    }
};