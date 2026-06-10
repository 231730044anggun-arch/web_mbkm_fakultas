<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pedoman_sops', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori');
            $table->string('file_path')->nullable();
            $table->integer('tahun')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pedoman_sops'); }
};