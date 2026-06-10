<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pembimbing_lapangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_magangs')->onDelete('cascade');
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('instansi')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pembimbing_lapangans'); }
};