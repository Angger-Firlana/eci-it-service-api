<?php

namespace App\Domains\Invoice\Support;

use App\Enums\ServiceRequestStatusCode;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use Carbon\Carbon;

class CompletedAtResolver
{
    public function resolve(ServiceRequest $serviceRequest): ?Carbon
    {
        $completedLog = AuditLog::where('entity_type_id', 1)
            ->where('entity_id', $serviceRequest->id)
            ->whereHas('new_status', function ($query) {
                $query->where('code', ServiceRequestStatusCode::COMPLETED->value);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$completedLog?->created_at) {
            return null;
        }

        return $completedLog->created_at instanceof Carbon
            ? $completedLog->created_at
            : Carbon::parse($completedLog->created_at);
    }
}
