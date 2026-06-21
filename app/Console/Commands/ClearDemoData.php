<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ClearDemoData extends Command
{
    protected $signature = 'mbkm:clear-demo-data {--with-master : Ikut hapus master data personil dan mitra demo: mahasiswa, dosen, pembimbing lapangan, mitra/instansi}';

    protected $description = 'Mengosongkan data demo/testing MBKM dengan aman tanpa menghapus admin/superadmin, source code, migration, atau asset.';

    private array $summary = [];

    public function handle(): int
    {
        $withMaster = (bool) $this->option('with-master');

        $this->warn('PERINGATAN: Command ini akan menghapus data demo/testing/transaksi MBKM.');
        $this->line('Aman: struktur database, migration, source code, asset/logo, dan akun admin/superadmin tidak dihapus.');
        $this->line('Default: master data dipertahankan; akun login non-admin dilepas dari master lalu dihapus.');

        if ($withMaster) {
            $this->warn('Opsi --with-master aktif: hanya master data mahasiswa, dosen, pembimbing lapangan, dan mitra/instansi yang akan dikosongkan.');
            $this->line('Referensi akademik tetap aman: fakultas, program studi, kelas, angkatan, dan periode tidak dihapus.');
        }

        $confirmation = $this->ask('Ketik tepat "YA HAPUS DATA DEMO" untuk melanjutkan');

        if ($confirmation !== 'YA HAPUS DATA DEMO') {
            $this->info('Dibatalkan. Tidak ada data yang dihapus.');
            return self::SUCCESS;
        }

        try {
            Schema::disableForeignKeyConstraints();

            DB::transaction(function () use ($withMaster) {
                $this->deleteUploadedFiles();
                $this->clearTransactionalTables();
                $this->detachAndDeleteNonAdminUsers();

                if ($withMaster) {
                    $this->clearMasterTables();
                }
            });
        } catch (Throwable $exception) {
            $this->error('Gagal menghapus data demo: '.$exception->getMessage());
            report($exception);
            return self::FAILURE;
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->newLine();
        $this->info('Clear demo data selesai.');
        $this->table(['Data', 'Jumlah'], collect($this->summary)->map(fn ($count, $label) => [$label, $count])->all());
        $this->line('Catatan: lakukan backup database sebelum menjalankan command ini di server/hosting.');

        return self::SUCCESS;
    }

    private function clearTransactionalTables(): void
    {
        $tables = [
            'activity_logs',
            'notifikasis',
            'status_histories',
            'penilaian_details',
            'penilaians',
            'bimbingan_formals',
            'bimbingans',
            'absensi_magangs',
            'logbooks',
            'laporan_kukertas',
            'kelayakan_seminars',
            'dokumens',
            'pengajuan_magangs',
        ];

        foreach ($tables as $table) {
            $this->deleteTableRows($table);
        }
    }

    private function detachAndDeleteNonAdminUsers(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $roles = ['mahasiswa', 'dosen', 'pembimbing_lapangan', 'mitra'];
        $userIds = DB::table('users')->whereIn('role', $roles)->pluck('id');

        if ($userIds->isEmpty()) {
            $this->summary['user non-admin dihapus'] = 0;
            return;
        }

        foreach (['mahasiswa_profiles', 'dosens', 'pembimbing_lapangans'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                DB::table($table)->whereIn('user_id', $userIds)->update(['user_id' => null]);
            }
        }

        $deleted = DB::table('users')->whereIn('id', $userIds)->delete();
        $this->summary['user non-admin dihapus'] = $deleted;
    }

    private function clearMasterTables(): void
    {
        $tables = [
            'mitra_users',
            'pembimbing_lapangans',
            'dosens',
            'mahasiswa_profiles',
            'mitras',
        ];

        foreach ($tables as $table) {
            $this->deleteTableRows($table, 'master');
        }
    }

    private function deleteUploadedFiles(): void
    {
        $paths = collect();

        $paths = $paths->merge($this->collectFileColumn('dokumens', 'file_path'));
        $paths = $paths->merge($this->collectFileColumn('logbooks', 'bukti_foto'));
        $paths = $paths->merge($this->collectFileColumn('absensi_magangs', 'bukti_hadir'));
        $paths = $paths->merge($this->collectFileColumn('bimbingan_formals', 'lampiran'));
        $paths = $paths->merge($this->collectFileColumn('kelayakan_seminars', 'laporan_hasil_magang'));
        $paths = $paths->merge($this->collectFileColumn('kelayakan_seminars', 'produk_magang'));
        $paths = $paths->merge($this->collectFileColumn('laporan_kukertas', 'laporan_kukerta'));

        if (Schema::hasTable('laporan_kukertas') && Schema::hasColumn('laporan_kukertas', 'dokumentasi_kukerta')) {
            DB::table('laporan_kukertas')
                ->whereNotNull('dokumentasi_kukerta')
                ->pluck('dokumentasi_kukerta')
                ->each(function ($json) use (&$paths) {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) {
                        $paths = $paths->merge($decoded);
                    }
                });
        }

        $deleted = 0;

        $paths->filter()
            ->map(fn ($path) => str_replace('\\', '/', trim((string) $path)))
            ->unique()
            ->each(function (string $path) use (&$deleted) {
                if ($this->isSafePublicUploadPath($path) && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    $deleted++;
                }
            });

        $this->summary['file upload testing dihapus'] = $deleted;
    }

    private function collectFileColumn(string $table, string $column)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return collect();
        }

        return DB::table($table)->whereNotNull($column)->pluck($column);
    }

    private function isSafePublicUploadPath(string $path): bool
    {
        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        $blocked = ['.', '/', '\\', 'app.php', '.env'];

        if (in_array($path, $blocked, true)) {
            return false;
        }

        return !str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:[\/\\\\]/', $path);
    }

    private function deleteTableRows(string $table, string $prefix = 'transaksi'): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $count = DB::table($table)->count();
        DB::table($table)->delete();

        $this->summary[$prefix.' '.$table] = $count;
    }
}
