<?php

namespace App\Domains\AuditLog\Actions;

use App\Models\ServiceRequest;

class CreateServiceRequestAuditLog
{
    public function __construct(
        protected CreateAuditLog $createAuditLog
    ) {
    }

    public function execute(ServiceRequest $serviceRequest, string $action, string $notes): void
    {
        $this->createAuditLog->execute([
            'actor_id' => auth()->id() ?? $serviceRequest->user_id,
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'action' => $action,
            'notes' => $notes,
            'old_status_id' => $serviceRequest->status_id,
            'new_status_id' => $serviceRequest->status_id,
        ]);
    }
}
