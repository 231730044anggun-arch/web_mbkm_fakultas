<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kukertas', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_kukertas', 'foto_dokumentasi_kukerta')) {
                $table->json('foto_dokumentasi_kukerta')->nullable()->after('dokumentasi_kukerta');
            }
            if (!Schema::hasColumn('laporan_kukertas', 'output_kukerta_file')) {
                $table->string('output_kukerta_file')->nullable()->after('laporan_kukerta');
            }
            if (!Schema::hasColumn('laporan_kukertas', 'output_kukerta_link')) {
                $table->string('output_kukerta_link')->nullable()->after('output_kukerta_file');
            }
        });

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kelayakan_seminars', 'draft_jurnal')) {
                $table->string('draft_jurnal')->nullable()->after('produk_magang');
            }
        });

        Schema::table('penilaians', function (Blueprint $table) {
            if (!Schema::hasColumn('penilaians', 'file_penilaian_formal_dosen')) {
                $table->string('file_penilaian_formal_dosen')->nullable()->after('catatan_dosen');
            }
            if (!Schema::hasColumn('penilaians', 'file_penilaian_formal_pembimbing')) {
                $table->string('file_penilaian_formal_pembimbing')->nullable()->after('file_penilaian_formal_dosen');
            }
        });

        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','proposal_magang','laporan','surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar','laporan_hasil_magang','produk_magang','laporan_kukerta','draft_jurnal','output_kukerta','file_penilaian_formal_dosen','file_penilaian_formal_pembimbing') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','proposal_magang','laporan','surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar','laporan_hasil_magang','produk_magang','laporan_kukerta') NOT NULL");

        Schema::table('penilaians', function (Blueprint $table) {
            if (Schema::hasColumn('penilaians', 'file_penilaian_formal_pembimbing')) {
                $table->dropColumn('file_penilaian_formal_pembimbing');
            }
            if (Schema::hasColumn('penilaians', 'file_penilaian_formal_dosen')) {
                $table->dropColumn('file_penilaian_formal_dosen');
            }
        });

        Schema::table('kelayakan_seminars', function (Blueprint $table) {
            if (Schema::hasColumn('kelayakan_seminars', 'draft_jurnal')) {
                $table->dropColumn('draft_jurnal');
            }
        });

        Schema::table('laporan_kukertas', function (Blueprint $table) {
            foreach (['output_kukerta_link', 'output_kukerta_file', 'foto_dokumentasi_kukerta'] as $column) {
                if (Schema::hasColumn('laporan_kukertas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
