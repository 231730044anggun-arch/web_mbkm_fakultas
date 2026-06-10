<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_magangs')->onDelete('cascade');
            $table->float('nilai_lapangan')->nullable();
            $table->float('nilai_dosen')->nullable();
            $table->float('nilai_akhir')->nullable();
            $table->string('grade')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('penilaians'); }
};