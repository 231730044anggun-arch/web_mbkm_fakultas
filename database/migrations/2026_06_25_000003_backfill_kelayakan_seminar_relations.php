<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kelayakan_seminars') || !Schema::hasTable('pengajuan_magangs')) {
            return;
        }
        if (Schema::hasColumn('kelayakan_seminars', 'status')) {
            DB::table('kelayakan_seminars')
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '');
                })
                ->update(['status' => 'menunggu_persetujuan']);
        }
        if (Schema::hasTable('bimbingans') && Schema::hasColumn('kelayakan_seminars', 'dosen_id')) {
            // Backfill dosen only when exactly one unique dosen candidate exists for the same pengajuan.
            DB::statement(<<<'SQL'
                UPDATE kelayakan_seminars ks
                JOIN (
                    SELECT b.pengajuan_id, b.dosen_id
                    FROM bimbingans b
                    JOIN (
                        SELECT pengajuan_id
                        FROM bimbingans
                        WHERE dosen_id IS NOT NULL
                        GROUP BY pengajuan_id
                        HAVING COUNT(DISTINCT dosen_id) = 1
                    ) unique_dosen ON unique_dosen.pengajuan_id = b.pengajuan_id
                    WHERE b.dosen_id IS NOT NULL
                    GROUP BY b.pengajuan_id, b.dosen_id
                ) candidate ON candidate.pengajuan_id = ks.pengajuan_id
                SET ks.dosen_id = candidate.dosen_id
                WHERE ks.dosen_id IS NULL
            SQL);
        }
        if (
            Schema::hasColumn('kelayakan_seminars', 'pembimbing_lapangan_id')
            && Schema::hasColumn('pengajuan_magangs', 'pembimbing_lapangan_id')
        ) {
            // Pengajuan magang stores one explicit pembimbing lapangan candidate; null candidates are skipped.
            DB::statement(<<<'SQL'
                UPDATE kelayakan_seminars ks
                JOIN pengajuan_magangs p ON p.id = ks.pengajuan_id
                SET ks.pembimbing_lapangan_id = p.pembimbing_lapangan_id
                WHERE ks.pembimbing_lapangan_id IS NULL
                  AND p.pembimbing_lapangan_id IS NOT NULL
            SQL);
        }
    }
    public function down(): void
    {
        // Backfill only; no destructive rollback.
    }
};