<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_magangs', 'pengajuan_awal_id')) {
                $table->foreignId('pengajuan_awal_id')
                    ->nullable()
                    ->after('mahasiswa_id')
                    ->constrained('pengajuan_magangs')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_magangs', 'pengajuan_awal_id')) {
                $table->dropConstrainedForeignId('pengajuan_awal_id');
            }
        });
    }
};
