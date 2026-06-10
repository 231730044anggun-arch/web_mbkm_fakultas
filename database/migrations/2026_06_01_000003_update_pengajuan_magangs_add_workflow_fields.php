<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_magangs', 'jenis_mitra')) {
                $table->string('jenis_mitra')->nullable()->after('jenis_magang');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'status_surat_pengantar')) {
                $table->string('status_surat_pengantar')->default('belum_ada')->after('status_pengajuan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'status_laporan')) {
                $table->string('status_laporan')->default('belum_ada')->after('status_surat_pengantar');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'status_seminar')) {
                $table->string('status_seminar')->default('belum')->after('status_laporan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'judul_laporan')) {
                $table->string('judul_laporan')->nullable()->after('status_seminar');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'seminar_tanggal')) {
                $table->date('seminar_tanggal')->nullable()->after('judul_laporan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'seminar_jam')) {
                $table->time('seminar_jam')->nullable()->after('seminar_tanggal');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'seminar_ruangan')) {
                $table->string('seminar_ruangan')->nullable()->after('seminar_jam');
            }
        });
    }

    public function down(): void {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            $table->dropColumn(['jenis_mitra', 'status_surat_pengantar', 'status_laporan', 'status_seminar', 'judul_laporan', 'seminar_tanggal', 'seminar_jam', 'seminar_ruangan']);
        });
    }
};