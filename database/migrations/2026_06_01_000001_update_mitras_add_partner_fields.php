<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mitras', function (Blueprint $table) {
            if (!Schema::hasColumn('mitras', 'jenis_mitra')) {
                $table->string('jenis_mitra')->default('non_mou')->after('status_mitra');
            }
            if (!Schema::hasColumn('mitras', 'status_mitra_detail')) {
                $table->string('status_mitra_detail')->default('aktif')->after('jenis_mitra');
            }
            if (!Schema::hasColumn('mitras', 'pembimbing_lapangan_nama')) {
                $table->string('pembimbing_lapangan_nama')->nullable()->after('file_mou');
            }
            if (!Schema::hasColumn('mitras', 'pembimbing_lapangan_jabatan')) {
                $table->string('pembimbing_lapangan_jabatan')->nullable()->after('pembimbing_lapangan_nama');
            }
            if (!Schema::hasColumn('mitras', 'pembimbing_lapangan_kontak')) {
                $table->string('pembimbing_lapangan_kontak')->nullable()->after('pembimbing_lapangan_jabatan');
            }
        });
    }

    public function down(): void {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn(['jenis_mitra', 'status_mitra_detail', 'pembimbing_lapangan_nama', 'pembimbing_lapangan_jabatan', 'pembimbing_lapangan_kontak']);
        });
    }
};