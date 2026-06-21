<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','proposal_magang','laporan','surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar','laporan_hasil_magang','produk_magang','laporan_kukerta') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','proposal_magang','laporan','surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar') NOT NULL");
    }
};
