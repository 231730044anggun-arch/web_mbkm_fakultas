<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Models\Bimbingan;
use App\Models\Logbook;
use App\Models\PengajuanMagang;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LogbookBuktiController extends Controller
{
    use HandlesSecurePublicFiles;
    public function preview(Logbook $logbook)
    {
        $logbook->loadMissing('pengajuan.mahasiswa', 'pengajuan.pembimbingLapangan', 'pengajuan.mitra.mitraUsers.user');
        abort_unless($this->canAccess($logbook), 403);

        $path = $this->normalizePublicPath($logbook->bukti_foto);
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        $filename = 'Bukti Logbook ' . ($logbook->pengajuan?->mahasiswa?->nama_lengkap ?? 'Mahasiswa') . '.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

        return $this->inlineFile($path, $filename);
    }

    private function canAccess(Logbook $logbook): bool
    {
        $user = auth()->user();
        if (!$user || !$logbook->pengajuan) {
            return false;
        }

        if (in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        if ($user->role === 'mahasiswa') {
            return $this->idsMatch($logbook->pengajuan->mahasiswa_id, $user->mahasiswaProfile?->id);
        }

        if ($user->role === 'dosen') {
            return Bimbingan::where('pengajuan_id', $logbook->pengajuan_id)
                ->where('dosen_id', $user->dosen?->id)
                ->exists();
        }

        if ($user->role === 'pembimbing_lapangan') {
            return $this->idsMatch($logbook->pengajuan->pembimbing_lapangan_id, $user->pembimbingLapangan?->id);
        }

        if ($user->role === 'mitra') {
            return PengajuanMagang::whereKey($logbook->pengajuan_id)
                ->whereHas('mitra.mitraUsers', fn($query) => $query->where('user_id', $user->id))
                ->exists();
        }

        return false;
    }

    private function normalizePublicPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = ltrim($path, '/');

        foreach (['storage/app/public/', 'public/storage/', 'storage/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return ltrim($path, '/');
    }

    private function inlineFile(string $path, string $filename)
    {
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);

        return response()->stream(function () use ($absolutePath) {
            readfile($absolutePath);
        }, 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Length' => (string) filesize($absolutePath),
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
