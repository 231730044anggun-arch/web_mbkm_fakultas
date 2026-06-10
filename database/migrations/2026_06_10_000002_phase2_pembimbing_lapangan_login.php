<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_magangs', 'pembimbing_lapangan_id')) {
                $table->foreignId('pembimbing_lapangan_id')
                    ->nullable()
                    ->after('mitra_id')
                    ->constrained('pembimbing_lapangans')
                    ->nullOnDelete();
            }
        });

        Schema::table('absensi_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi_magangs', 'pembimbing_lapangan_id')) {
                $table->foreignId('pembimbing_lapangan_id')
                    ->nullable()
                    ->after('mitra_id')
                    ->constrained('pembimbing_lapangans')
                    ->nullOnDelete();
            }
        });

        Schema::table('pembimbing_lapangans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembimbing_lapangans', 'email_akses_terkirim')) {
                $table->boolean('email_akses_terkirim')->default(false)->after('catatan');
            }
            if (!Schema::hasColumn('pembimbing_lapangans', 'last_email_sent_at')) {
                $table->timestamp('last_email_sent_at')->nullable()->after('email_akses_terkirim');
            }
        });

        if (Schema::hasColumn('pengajuan_magangs', 'pembimbing_lapangan_id')) {
            DB::statement("
                UPDATE pengajuan_magangs p
                JOIN pembimbing_lapangans pl ON pl.pengajuan_id = p.id
                SET p.pembimbing_lapangan_id = pl.id
                WHERE p.pembimbing_lapangan_id IS NULL
            ");
        }

        if (Schema::hasColumn('absensi_magangs', 'pembimbing_lapangan_id')) {
            DB::statement("
                UPDATE absensi_magangs a
                JOIN pengajuan_magangs p ON p.id = a.pengajuan_magang_id
                SET a.pembimbing_lapangan_id = p.pembimbing_lapangan_id
                WHERE a.pembimbing_lapangan_id IS NULL
                  AND p.pembimbing_lapangan_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_magangs', 'pembimbing_lapangan_id')) {
                $table->dropConstrainedForeignId('pembimbing_lapangan_id');
            }
        });

        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_magangs', 'pembimbing_lapangan_id')) {
                $table->dropConstrainedForeignId('pembimbing_lapangan_id');
            }
        });

        Schema::table('pembimbing_lapangans', function (Blueprint $table) {
            foreach (['last_email_sent_at', 'email_akses_terkirim'] as $column) {
                if (Schema::hasColumn('pembimbing_lapangans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};