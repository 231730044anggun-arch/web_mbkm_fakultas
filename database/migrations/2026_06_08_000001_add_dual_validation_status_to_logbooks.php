<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'status_dosen')) {
                $table->string('status_dosen', 20)->default('pending')->after('status_validasi');
            }

            if (!Schema::hasColumn('logbooks', 'catatan_mitra')) {
                $table->text('catatan_mitra')->nullable()->after('catatan_dosen');
            }

            if (!Schema::hasColumn('logbooks', 'status_mitra')) {
                $table->string('status_mitra', 20)->default('pending')->after('catatan_mitra');
            }
        });

        DB::table('logbooks')
            ->select('id', 'status_validasi')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $status = $row->status_validasi ?: 'pending';

                    DB::table('logbooks')
                        ->where('id', $row->id)
                        ->update([
                            'status_dosen' => $status,
                            'status_mitra' => $status === 'disetujui' ? 'disetujui' : 'pending',
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (Schema::hasColumn('logbooks', 'status_mitra')) {
                $table->dropColumn('status_mitra');
            }

            if (Schema::hasColumn('logbooks', 'catatan_mitra')) {
                $table->dropColumn('catatan_mitra');
            }

            if (Schema::hasColumn('logbooks', 'status_dosen')) {
                $table->dropColumn('status_dosen');
            }
        });
    }
};
