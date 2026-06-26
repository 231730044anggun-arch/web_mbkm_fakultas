<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelayakan_seminar_catatan_histories')) {
            return;
        }

        Schema::create('kelayakan_seminar_catatan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelayakan_seminar_id')->constrained('kelayakan_seminars')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role_pemberi', 50);
            $table->string('nama_pemberi')->nullable();
            $table->string('status_tindakan', 30);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelayakan_seminar_catatan_histories');
    }
};
