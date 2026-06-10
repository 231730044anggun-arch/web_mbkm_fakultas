<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pengajuan_magangs MODIFY status_pengajuan ENUM('pending','disetujui','revisi','ditolak','berjalan','selesai','dibatalkan') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pengajuan_magangs MODIFY status_pengajuan ENUM('pending','disetujui','revisi','ditolak','berjalan','selesai') DEFAULT 'pending'");
    }
};