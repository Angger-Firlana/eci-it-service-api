<?php

namespace App\Domains\AuditLog\Actions;

use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class GetTimelineForServiceRequest
{
    public function execute($auditLogs, ServiceRequest $serviceRequest): array
    {
        return $auditLogs->map(function ($log) use ($serviceRequest) {
            $label = $log->action === 'CREATE_REQUEST'
                ? ($serviceRequest->status->name ?? 'Menunggu Approval')
                : ($log->action === 'UPDATE_STATUS' ? 'Status diubah' : $log->action);

            $actorName = $log->actor->name ?? 'Unknown';
            $description = $log->action === 'CREATE_REQUEST'
                ? "Request dibuat oleh {$actorName}"
                : $log->notes;

            return [
                'id' => $log->id,
                'label' => $label,
                'status' => $label,
                'date' => $log->created_at,
                'created_at' => $log->created_at,
                'note' => $description,
                'description' => $description,
                'state' => 'active',
            ];
        })->values()->all();
    }
}
