<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Models\ServiceCost;
use Illuminate\Support\Facades\Storage;

class GetAttachment
{
    public function execute(int $serviceRequestId, int $costId)
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId != $cost->service_request_id) {
            throw new \Exception('Service request id not match');
        }

        $path = $cost->image_path;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'Attachment not found');
        }

        return response()->file(public_path('images/' . $path));
    }
}

