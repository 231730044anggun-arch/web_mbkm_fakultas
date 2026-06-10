<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_magangs', 'jenis_pengajuan')) {
                $table->string('jenis_pengajuan')->default('surat_pengantar')->after('mahasiswa_id');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'status_surat_keterangan')) {
                $table->string('status_surat_keterangan')->default('belum_ada')->after('status_surat_pengantar');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'nomor_surat_balasan')) {
                $table->string('nomor_surat_balasan')->nullable()->after('status_surat_keterangan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'tanggal_surat_balasan')) {
                $table->date('tanggal_surat_balasan')->nullable()->after('nomor_surat_balasan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'catatan_mahasiswa')) {
                $table->text('catatan_mahasiswa')->nullable()->after('catatan_admin');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'ringkasan_seminar')) {
                $table->text('ringkasan_seminar')->nullable()->after('judul_laporan');
            }
            if (!Schema::hasColumn('pengajuan_magangs', 'usulan_tanggal_seminar')) {
                $table->date('usulan_tanggal_seminar')->nullable()->after('ringkasan_seminar');
            }
        });

        DB::statement("ALTER TABLE pengajuan_magangs MODIFY status_pengajuan ENUM('pending','disetujui','revisi','ditolak','berjalan','selesai') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','proposal_magang','laporan','surat_pengantar','surat_keterangan_magang','sk_magang','surat_seminar') NOT NULL");

        Schema::table('notifikasis', function (Blueprint $table) {
            if (!Schema::hasColumn('notifikasis', 'target_url')) {
                $table->string('target_url')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pengajuan',
                'status_surat_keterangan',
                'nomor_surat_balasan',
                'tanggal_surat_balasan',
                'catatan_mahasiswa',
                'ringkasan_seminar',
                'usulan_tanggal_seminar',
            ]);
        });

        DB::statement("ALTER TABLE pengajuan_magangs MODIFY status_pengajuan ENUM('pending','disetujui','ditolak','berjalan','selesai') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE dokumens MODIFY jenis_dokumen ENUM('surat_permohonan','surat_diterima','laporan','surat_keterangan_magang','sk_magang','surat_seminar') NOT NULL");

        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropColumn('target_url');
        });
    }
};
