<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kelas')) {
            Schema::create('kelas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kelas')->unique();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('angkatans')) {
            Schema::create('angkatans', function (Blueprint $table) {
                $table->id();
                $table->integer('tahun')->unique();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->timestamps();
            });
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $kelas) {
            DB::table('kelas')->updateOrInsert(['nama_kelas' => $kelas], ['status' => 'aktif', 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach (range(2021, 2027) as $tahun) {
            DB::table('angkatans')->updateOrInsert(['tahun' => $tahun], ['status' => 'aktif', 'updated_at' => now(), 'created_at' => now()]);
        }

        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa_profiles', 'kelas_id')) {
                $table->foreignId('kelas_id')->nullable()->after('kelas')->constrained('kelas')->nullOnDelete();
            }
            if (!Schema::hasColumn('mahasiswa_profiles', 'angkatan_id')) {
                $table->foreignId('angkatan_id')->nullable()->after('angkatan')->constrained('angkatans')->nullOnDelete();
            }
            if (!Schema::hasColumn('mahasiswa_profiles', 'profile_status')) {
                $table->enum('profile_status', ['belum_lengkap', 'lengkap'])->default('belum_lengkap')->after('status_mahasiswa');
            }
        });

        Schema::table('dosens', function (Blueprint $table) {
            if (!Schema::hasColumn('dosens', 'profile_status')) {
                $table->enum('profile_status', ['belum_lengkap', 'lengkap'])->default('belum_lengkap')->after('status_dosen');
            }
        });

        Schema::table('pembimbing_lapangans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembimbing_lapangans', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pembimbing_lapangans', 'mitra_id')) {
                $table->foreignId('mitra_id')->nullable()->after('user_id')->constrained('mitras')->nullOnDelete();
            }
            if (!Schema::hasColumn('pembimbing_lapangans', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('instansi');
            }
            if (!Schema::hasColumn('pembimbing_lapangans', 'profile_status')) {
                $table->enum('profile_status', ['belum_lengkap', 'lengkap'])->default('belum_lengkap')->after('status');
            }
            if (!Schema::hasColumn('pembimbing_lapangans', 'catatan')) {
                $table->text('catatan')->nullable()->after('profile_status');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin','mahasiswa','dosen','mitra','pembimbing_lapangan') NOT NULL DEFAULT 'mahasiswa'");
            DB::statement('ALTER TABLE mahasiswa_profiles MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE dosens MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE pembimbing_lapangans MODIFY pengajuan_id BIGINT UNSIGNED NULL');
        }

        DB::table('mahasiswa_profiles')->whereNotNull('kelas')->whereNull('kelas_id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $kelasId = DB::table('kelas')->where('nama_kelas', $row->kelas)->value('id');
                if ($kelasId) DB::table('mahasiswa_profiles')->where('id', $row->id)->update(['kelas_id' => $kelasId]);
            }
        });

        DB::table('mahasiswa_profiles')->whereNotNull('angkatan')->whereNull('angkatan_id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $angkatanId = DB::table('angkatans')->where('tahun', $row->angkatan)->value('id');
                if ($angkatanId) DB::table('mahasiswa_profiles')->where('id', $row->id)->update(['angkatan_id' => $angkatanId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembimbing_lapangans', function (Blueprint $table) {
            foreach (['catatan', 'profile_status', 'status', 'mitra_id', 'user_id'] as $column) {
                if (Schema::hasColumn('pembimbing_lapangans', $column)) $table->dropColumn($column);
            }
        });
        Schema::table('dosens', function (Blueprint $table) {
            if (Schema::hasColumn('dosens', 'profile_status')) $table->dropColumn('profile_status');
        });
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            foreach (['profile_status', 'angkatan_id', 'kelas_id'] as $column) {
                if (Schema::hasColumn('mahasiswa_profiles', $column)) $table->dropColumn($column);
            }
        });
        Schema::dropIfExists('angkatans');
        Schema::dropIfExists('kelas');
    }
};