<?php

namespace App\Domains\AuditLog\Actions;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Collection;

class GetAuditLogsForServiceRequest
{
    public function execute(ServiceRequest $serviceRequest): Collection
    {
        return AuditLog::where('entity_type_id', 1)
            ->where('entity_id', $serviceRequest->id)
            ->with([
                'actor:id,name',
                'actor.departments:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
