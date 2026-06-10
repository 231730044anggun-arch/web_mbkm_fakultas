<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE mitras MODIFY status_mou ENUM('aktif','tidak','expired') NOT NULL DEFAULT 'tidak'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE mitras MODIFY status_mou ENUM('aktif','tidak') NOT NULL DEFAULT 'aktif'");
    }
};
