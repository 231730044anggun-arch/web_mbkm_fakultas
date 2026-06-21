<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bimbingan_formals', 'dosen_id')) {
            DB::statement('ALTER TABLE bimbingan_formals MODIFY dosen_id BIGINT UNSIGNED NULL');
        }

        Schema::table('bimbingan_formals', function (Blueprint $table) {
            if (!Schema::hasColumn('bimbingan_formals', 'tujuan_bimbingan')) {
                $table->string('tujuan_bimbingan', 40)->default('dosen_pembimbing')->after('mahasiswa_id');
            }
            if (!Schema::hasColumn('bimbingan_formals', 'pembimbing_lapangan_id')) {
                $table->foreignId('pembimbing_lapangan_id')->nullable()->after('dosen_id')->constrained('pembimbing_lapangans')->nullOnDelete();
            }
            if (!Schema::hasColumn('bimbingan_formals', 'balasan_pembimbing')) {
                $table->text('balasan_pembimbing')->nullable()->after('balasan_dosen');
            }
        });

        DB::table('bimbingan_formals')
            ->whereNull('tujuan_bimbingan')
            ->update(['tujuan_bimbingan' => 'dosen_pembimbing']);
    }

    public function down(): void
    {
        Schema::table('bimbingan_formals', function (Blueprint $table) {
            if (Schema::hasColumn('bimbingan_formals', 'balasan_pembimbing')) {
                $table->dropColumn('balasan_pembimbing');
            }
            if (Schema::hasColumn('bimbingan_formals', 'pembimbing_lapangan_id')) {
                $table->dropConstrainedForeignId('pembimbing_lapangan_id');
            }
            if (Schema::hasColumn('bimbingan_formals', 'tujuan_bimbingan')) {
                $table->dropColumn('tujuan_bimbingan');
            }
        });

        if (Schema::hasColumn('bimbingan_formals', 'dosen_id')) {
            DB::statement('ALTER TABLE bimbingan_formals MODIFY dosen_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
