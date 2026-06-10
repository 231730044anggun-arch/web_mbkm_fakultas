<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi');
            $table->string('jenis_instansi')->nullable();
            $table->string('bidang_industri')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('email')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('website')->nullable();
            $table->enum('status_mitra', ['terdaftar', 'tidak'])->default('terdaftar');
            $table->string('nomor_mou')->nullable();
            $table->date('tanggal_mulai_mou')->nullable();
            $table->date('tanggal_berakhir_mou')->nullable();
            $table->string('file_mou')->nullable();
            $table->enum('status_mou', ['aktif', 'tidak'])->default('aktif');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mitras'); }
};