<?php

namespace App\Domains\ServiceRequestCost\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CostAttachmentStorage
{
    public function store(int $serviceRequestId, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::lower(Str::random(6));
        $filename = "sr{$serviceRequestId}_receipt_{$timestamp}_{$random}.{$extension}";
        $directory = 'service-costs';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            $publicDisk->delete($path);
            return;
        }

        $legacyPath = public_path('images/' . basename($path));
        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }
}
