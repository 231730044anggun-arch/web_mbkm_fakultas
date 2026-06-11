<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kelayakan_seminars')) {
            Schema::create('kelayakan_seminars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengajuan_id')->constrained('pengajuan_magangs')->cascadeOnDelete();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswa_profiles')->cascadeOnDelete();
                $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->foreignId('pembimbing_lapangan_id')->nullable()->constrained('pembimbing_lapangans')->nullOnDelete();
                $table->string('laporan_hasil_magang');
                $table->text('output_magang');
                $table->string('produk_magang')->nullable();
                $table->text('catatan_mahasiswa')->nullable();
                $table->string('status_persetujuan_dosen', 20)->default('menunggu');
                $table->text('catatan_dosen')->nullable();
                $table->timestamp('tanggal_persetujuan_dosen')->nullable();
                $table->string('status_persetujuan_pembimbing', 20)->default('menunggu');
                $table->text('catatan_pembimbing')->nullable();
                $table->timestamp('tanggal_persetujuan_pembimbing')->nullable();
                $table->timestamps();
                $table->unique('pengajuan_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelayakan_seminars');
    }
};