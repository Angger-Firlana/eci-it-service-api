<?php

namespace App\Domains\ServiceRequest\Actions;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Domains\AuditLog\Services\AuditLogService;

class WriteAuditLogs
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public static function execute(ServiceRequest $serviceRequest, Status $newStatus,string $action, ?string $Lognotes = null): void
    {
        $auditLogService = app(AuditLogService::class);
        $auditLogService->createAuditLog([
            'actor_id' => auth()->id() ?? $serviceRequest->user_id,
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'action' => $action,
            'notes' => $Lognotes ?? "Status {$serviceRequest->status->name} to {$newStatus->name}",
            'old_status_id' => $serviceRequest->status_id,
            'new_status_id' => $newStatus->id,
        ]);
    }
}
