<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bimbingan_formals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_magangs')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa_profiles')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');
            $table->date('tanggal_bimbingan');
            $table->string('topik');
            $table->text('catatan_mahasiswa')->nullable();
            $table->string('lampiran')->nullable();
            $table->text('balasan_dosen')->nullable();
            $table->enum('status', ['menunggu_balasan', 'dibalas', 'selesai'])->default('menunggu_balasan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan_formals');
    }
};
