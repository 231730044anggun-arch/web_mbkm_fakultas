<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait HandlesSecurePublicFiles
{
    protected function normalizePublicPath(?string $path): ?string
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

    protected function publicFileExists(?string $path): bool
    {
        $path = $this->normalizePublicPath($path);
        return $path && Storage::disk('public')->exists($path);
    }

    protected function publicInlineResponse(?string $path, ?string $filename = null)
    {
        $path = $this->normalizePublicPath($path);
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);
        $filename = $filename ?: basename($path);

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

    protected function publicDownloadResponse(?string $path, ?string $filename = null)
    {
        $path = $this->normalizePublicPath($path);
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $filename ?: basename($path));
    }

    protected function deletePublicFileIfExists(?string $path): void
    {
        $path = $this->normalizePublicPath($path);
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}