<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nidn')->nullable();
            $table->string('nama_dosen');
            $table->foreignId('prodi_id')->nullable()->constrained('prodis');
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dosens'); }
};