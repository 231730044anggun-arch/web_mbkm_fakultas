<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_magangs')->onDelete('cascade');
            $table->enum('jenis_dokumen', ['surat_permohonan', 'surat_diterima', 'laporan']);
            $table->string('file_path');
            $table->date('tanggal_upload')->nullable();
            $table->enum('status_verifikasi', ['pending', 'valid', 'revisi'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dokumens'); }
};