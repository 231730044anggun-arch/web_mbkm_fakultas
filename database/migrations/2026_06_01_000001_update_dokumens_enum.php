<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Add new document types to jenis_dokumen enum
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','laporan','surat_keterangan_magang','sk_magang','surat_seminar') NOT NULL");
    }

    public function down(): void {
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','laporan') NOT NULL");
    }
};
