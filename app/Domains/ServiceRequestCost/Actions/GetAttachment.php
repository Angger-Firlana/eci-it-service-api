<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Exceptions\ApiException;
use App\Models\ServiceCost;
use Illuminate\Support\Facades\Storage;

class GetAttachment
{
    public function execute(int $serviceRequestId, int $costId)
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId != $cost->service_request_id) {
            throw ApiException::badRequest('Service request id not match');
        }

        $path = $cost->image_path;
        if (!$path) {
            throw ApiException::notFound('Attachment not found');
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            return response()->file($publicDisk->path($path));
        }

        $legacyPath = public_path('images/' . basename($path));
        if (is_file($legacyPath)) {
            return response()->file($legacyPath);
        }

        throw ApiException::notFound('Attachment not found');
    }
}
