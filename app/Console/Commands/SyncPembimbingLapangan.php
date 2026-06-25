<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\PembimbingLapangan;
use App\Models\PengajuanMagang;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPembimbingLapangan extends Command
{
    protected $signature = 'mbkm:sync-pembimbing-lapangan
        {--dry-run : Tampilkan rencana perubahan tanpa menyimpan ke database}
        {--nim= : Batasi pengecekan untuk NIM tertentu}';

    protected $description = 'Menautkan pembimbing lapangan lama ke akun user yang sudah ada berdasarkan email tanpa membuat akun duplikat.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = [
            'checked' => 0,
            'dosen_accounts_used' => 0,
            'role_added' => 0,
            'relations_linked' => 0,
            'pembimbing_created' => 0,
            'empty_email' => 0,
            'failed' => 0,
        ];

        $this->warn('Sinkronisasi ini tidak menghapus data, tidak mengubah email, dan tidak mereset password.');
        $this->line($dryRun ? 'Mode: DRY RUN - perubahan hanya disimulasikan.' : 'Mode: EKSEKUSI - relasi yang belum lengkap akan ditautkan.');
        $this->newLine();

        $nim = trim((string) $this->option('nim'));
        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mitra', 'pembimbingLapangan', 'pembimbingLapanganLegacy', 'bimbingans.dosen.user'])
            ->when($nim !== '', fn($query) => $query->whereHas('mahasiswa', fn($mahasiswa) => $mahasiswa->where('nim', $nim)))
            ->orderBy('id')
            ->get();

        $seenDosenUsers = [];
        $seenRoleAdds = [];

        $runner = function () use ($pengajuans, $dryRun, &$stats, &$seenDosenUsers, &$seenRoleAdds): void {
            foreach ($pengajuans as $pengajuan) {
                $stats['checked']++;

                try {
                    $candidate = $this->resolveCandidate($pengajuan);
                    $email = $candidate['email'];
                    $user = $candidate['user'];

                    $this->printDetailHeader($pengajuan, $candidate);

                    if (!$email && !$user) {
                        $stats['empty_email']++;
                        $this->line('Aksi: Dilewati - email pembimbing lapangan kosong dan nama tidak cocok dengan akun/dosen yang ada.');
                        $this->newLine();
                        continue;
                    }

                    if (!$user && $email) {
                        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
                    }

                    if (!$email && $user) {
                        $email = $this->normalizeEmail($user->email);
                    }

                    $hadPembimbingProfile = $user ? $user->pembimbingLapangan()->exists() : false;
                    $pembimbing = $this->resolvePembimbing($pengajuan, $email, $user, $dryRun, $stats);

                    if (!$pembimbing) {
                        $stats['failed']++;
                        $this->line('Aksi: Gagal - data pembimbing lapangan tidak dapat dibentuk.');
                        $this->newLine();
                        continue;
                    }

                    if ($user && ($user->role === 'dosen' || $user->dosen()->exists()) && !isset($seenDosenUsers[$user->id])) {
                        $seenDosenUsers[$user->id] = true;
                        $stats['dosen_accounts_used']++;
                    }

                    if ($user && !$hadPembimbingProfile && !isset($seenRoleAdds[$user->id])) {
                        $seenRoleAdds[$user->id] = true;
                        $stats['role_added']++;
                    }

                    $needsProfileLink = $user && !$hadPembimbingProfile;
                    $needsRelationLink = (int) $pengajuan->pembimbing_lapangan_id !== (int) $pembimbing->id;

                    if ($needsRelationLink) {
                        if (!$dryRun) {
                            $pengajuan->update(['pembimbing_lapangan_id' => $pembimbing->id]);
                            $pengajuan->absensis()->whereNull('pembimbing_lapangan_id')->update(['pembimbing_lapangan_id' => $pembimbing->id]);
                        }

                        $stats['relations_linked']++;
                    }

                    if ($needsProfileLink && $needsRelationLink) {
                        $this->line('Aksi: Tambahkan akses Pembimbing Lapangan dan tautkan mahasiswa.');
                    } elseif ($needsProfileLink) {
                        $this->line('Aksi: Tambahkan akses Pembimbing Lapangan pada akun yang sudah ada.');
                    } elseif ($needsRelationLink) {
                        $this->line('Aksi: Tautkan mahasiswa ke Pembimbing Lapangan yang sudah ada.');
                    } else {
                        $this->line('Aksi: Tidak ada perubahan - relasi sudah sesuai.');
                    }
                    $this->newLine();
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $this->error("Pengajuan #{$pengajuan->id}: {$e->getMessage()}");
                }
            }
        };

        if ($dryRun) {
            $runner();
        } else {
            DB::transaction($runner);
        }

        $this->newLine();
        $this->info('Sinkronisasi Pembimbing Lapangan selesai.');
        $this->line('Penempatan diperiksa: ' . $stats['checked']);
        $this->line('Akun dosen digunakan sebagai Pembimbing Lapangan: ' . $stats['dosen_accounts_used']);
        $this->line('Role Pembimbing Lapangan ditambahkan: ' . $stats['role_added']);
        $this->line('Relasi mahasiswa berhasil ditautkan: ' . $stats['relations_linked']);
        $this->line('Data pembimbing lapangan baru dibuat: ' . $stats['pembimbing_created']);
        $this->line('Data dilewati karena email kosong: ' . $stats['empty_email']);
        $this->line('Data gagal diproses: ' . $stats['failed']);

        if ($dryRun) {
            $this->newLine();
            $this->warn('Dry-run selesai. Tidak ada perubahan yang disimpan ke database.');
        }

        return self::SUCCESS;
    }

    private function resolvePembimbing(PengajuanMagang $pengajuan, ?string $email, ?User $user, bool $dryRun, array &$stats): ?PembimbingLapangan
    {
        $existing = null;

        if ($user) {
            $existing = PembimbingLapangan::where('user_id', $user->id)->first();
        }

        $existing = $existing ?: ($email ? PembimbingLapangan::whereRaw('LOWER(email) = ?', [$email])->first() : null);

        if (!$existing) {
            $current = $pengajuan->pembimbingLapangan ?: $pengajuan->pembimbingLapanganLegacy;
            $currentEmail = $this->normalizeEmail($current?->email);
            $canReuseCurrent = $current && (
                (!$email && !$user)
                || ($email && $currentEmail === $email)
                || ($user && $current->user_id && (int) $current->user_id === (int) $user->id)
            );

            if ($canReuseCurrent) {
                $existing = $current;
            }
        }

        if (!$email) {
            $email = $this->normalizeEmail($existing?->email ?: $user?->email);
        }

        $attributes = [
            'user_id' => $existing?->user_id ?: $user?->id,
            'mitra_id' => $existing?->mitra_id ?: $pengajuan->mitra_id,
            'pengajuan_id' => $existing?->pengajuan_id ?: $pengajuan->id,
            'nama' => $existing?->nama ?: ($pengajuan->pic_nama ?: $pengajuan->mitra?->pembimbing_lapangan_nama ?: $user?->name ?: '-'),
            'jabatan' => $existing?->jabatan ?: ($pengajuan->pic_jabatan ?: $pengajuan->mitra?->pembimbing_lapangan_jabatan),
            'email' => $existing?->email ?: $email,
            'no_hp' => $existing?->no_hp ?: ($pengajuan->pic_no_hp ?: $pengajuan->mitra?->pembimbing_lapangan_kontak),
            'instansi' => $existing?->instansi ?: $pengajuan->mitra?->nama_instansi,
            'status' => $existing?->status ?: 'aktif',
        ];

        if ($existing) {
            if (!$dryRun) {
                $existing->fill($attributes);
                $existing->syncProfileStatus();
                $existing->save();
            }

            return $existing;
        }

        $stats['pembimbing_created']++;

        if ($dryRun) {
            $preview = new PembimbingLapangan($attributes);
            $preview->id = 0;

            return $preview;
        }

        $pembimbing = new PembimbingLapangan($attributes);
        $pembimbing->syncProfileStatus();
        $pembimbing->save();

        return $pembimbing;
    }

    private function resolveCandidate(PengajuanMagang $pengajuan): array
    {
        $email = $this->normalizeEmail(
            $pengajuan->pic_email
            ?: $pengajuan->mitra?->pembimbing_lapangan_email
            ?: $pengajuan->pembimbingLapangan?->email
            ?: $pengajuan->pembimbingLapanganLegacy?->email
        );

        $name = $this->normalizeName(
            $pengajuan->pic_nama
            ?: $pengajuan->mitra?->pembimbing_lapangan_nama
            ?: $pengajuan->pembimbingLapangan?->nama
            ?: $pengajuan->pembimbingLapanganLegacy?->nama
        );

        $user = $email ? User::whereRaw('LOWER(email) = ?', [$email])->first() : null;

        if (!$user && $name) {
            $dosen = Dosen::with('user')
                ->get()
                ->first(fn($dosen) => $this->nameMatches($this->normalizeName($dosen->nama_dosen), $name));

            $user = $dosen?->user;
            $email = $this->normalizeEmail($user?->email ?: $dosen?->email_dosen) ?: $email;
        }

        if (!$user && $name) {
            $user = User::get()->first(fn($candidate) => $this->nameMatches($this->normalizeName($candidate->name), $name));
            $email = $this->normalizeEmail($user?->email) ?: $email;
        }

        return [
            'email' => $email,
            'name' => $name,
            'user' => $user,
            'source' => $email ? 'email' : ($user ? 'nama' : 'tidak ditemukan'),
        ];
    }

    private function printDetailHeader(PengajuanMagang $pengajuan, array $candidate): void
    {
        $user = $candidate['user'];
        $dosen = $user?->dosen;
        $pembimbing = $user?->pembimbingLapangan;

        $this->line('NIM: ' . ($pengajuan->mahasiswa?->nim ?: '-'));
        $this->line('Mahasiswa: ' . ($pengajuan->mahasiswa?->nama_lengkap ?: '-'));
        $this->line('Instansi: ' . ($pengajuan->mitra?->nama_instansi ?: $pengajuan->nama_instansi_manual ?: '-'));
        $this->line('Email Pembimbing Lapangan: ' . ($candidate['email'] ?: '-'));
        $this->line('Nama Pembimbing Lapangan: ' . ($pengajuan->pic_nama ?: $pengajuan->mitra?->pembimbing_lapangan_nama ?: $pengajuan->pembimbingLapangan?->nama ?: $pengajuan->pembimbingLapanganLegacy?->nama ?: '-'));
        $this->line('User ditemukan: ' . ($user?->name ?: 'Tidak'));
        $this->line('Profil Dosen ditemukan: ' . ($dosen ? 'Ya' : 'Tidak'));
        $this->line('Profil Pembimbing Lapangan ditemukan: ' . ($pembimbing ? 'Ya' : 'Tidak'));
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = strtolower(trim(preg_replace('/\s+/u', '', (string) $email)));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function normalizeName(?string $name): ?string
    {
        $name = preg_replace('/\s+/u', ' ', trim(strtolower((string) $name)));
        $name = str_replace(['.', ',', '’', "'"], '', $name);

        return $name !== '' ? $name : null;
    }

    private function nameMatches(?string $left, ?string $right): bool
    {
        if (!$left || !$right) {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        return strlen($left) >= 8
            && strlen($right) >= 8
            && (str_contains($left, $right) || str_contains($right, $left));
    }
}
