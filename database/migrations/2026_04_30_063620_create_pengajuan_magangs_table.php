<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengajuan_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa_profiles');
            $table->foreignId('periode_id')->constrained('periodes');
            $table->enum('jenis_magang', ['mitra', 'mandiri']);
            $table->foreignId('mitra_id')->nullable()->constrained('mitras');
            $table->string('nama_instansi_manual')->nullable();
            $table->text('alamat_instansi_manual')->nullable();
            $table->string('kota_instansi_manual')->nullable();
            $table->string('posisi_magang');
            $table->text('deskripsi_kegiatan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('durasi')->nullable();
            $table->enum('status_pengajuan', ['pending','disetujui','ditolak','berjalan','selesai'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pengajuan_magangs'); }
};