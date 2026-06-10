<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'output_kegiatan')) {
                $table->text('output_kegiatan')->nullable()->after('kegiatan');
            }

            if (!Schema::hasColumn('logbooks', 'kendala')) {
                $table->text('kendala')->nullable()->after('output_kegiatan');
            }

            if (!Schema::hasColumn('logbooks', 'solusi')) {
                $table->text('solusi')->nullable()->after('kendala');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['output_kegiatan', 'kendala', 'solusi']);
        });
    }
};
