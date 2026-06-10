<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pengajuan_id')
                  ->constrained('pengajuan_magangs')
                  ->onDelete('cascade');

            $table->date('tanggal');

            $table->text('kegiatan');

            $table->time('jam_mulai')->nullable();

            $table->time('jam_selesai')->nullable();

            $table->string('bukti_foto')->nullable();

            $table->enum('status_validasi', [
                'pending',
                'disetujui',
                'revisi'
            ])->default('pending');

            $table->text('catatan_dosen')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};