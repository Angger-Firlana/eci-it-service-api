<?php

namespace App\Domains\AuditLog\Actions;

use App\Models\ServiceRequest;
use App\Models\Status;

class CreateStatusAuditLog
{
    public function __construct(
        protected CreateAuditLog $createAuditLog
    ) {
    }

    public function execute(
        ServiceRequest $serviceRequest,
        Status $status,
        int $oldStatusId,
        int $newStatusId,
        array $data
    ): void {
        if ($newStatusId == $oldStatusId) {
            return;
        }

        $this->createAuditLog->execute([
            'actor_id' => auth()->id() ?? $serviceRequest->user_id,
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'action' => 'UPDATE_STATUS',
            'notes' => $data['log_notes'] ?? "Status changed from {$serviceRequest->status->name} to {$status->name}",
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
        ]);
    }
}
