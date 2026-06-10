<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa_profiles', 'kelas')) {
                $table->string('kelas')->nullable()->after('nama_lengkap');
            }
            if (!Schema::hasColumn('mahasiswa_profiles', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('mahasiswa_profiles', 'tempat_lahir')) {
                $table->dropColumn('tempat_lahir');
            }
            if (Schema::hasColumn('mahasiswa_profiles', 'kelas')) {
                $table->dropColumn('kelas');
            }
        });
    }
};
