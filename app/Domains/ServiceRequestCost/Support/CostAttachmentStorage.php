<?php

namespace App\Domains\ServiceRequestCost\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CostAttachmentStorage
{
    public function store(int $serviceRequestId, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::lower(Str::random(6));
        $filename = "sr{$serviceRequestId}_receipt_{$timestamp}_{$random}.{$extension}";

        $file->move(public_path('images/'), $filename);

        return $filename;
    }
}

