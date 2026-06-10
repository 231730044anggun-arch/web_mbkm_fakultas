<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_magangs', 'pic_nama')) {
                $table->string('pic_nama')->nullable()->after('catatan_mahasiswa');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'pic_jabatan')) {
                $table->string('pic_jabatan')->nullable()->after('pic_nama');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'pic_no_hp')) {
                $table->string('pic_no_hp')->nullable()->after('pic_jabatan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'pic_email')) {
                $table->string('pic_email')->nullable()->after('pic_no_hp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            foreach (['pic_email', 'pic_no_hp', 'pic_jabatan', 'pic_nama'] as $column) {
                if (Schema::hasColumn('pengajuan_magangs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
