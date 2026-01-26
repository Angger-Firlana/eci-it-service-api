<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    private function createAuditLog(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function createStatusAuditLog(
        ServiceRequest $serviceRequest,
        Status $status,
        int $oldStatusId,
        int $newStatusId,
        array $data
    ): void {
        if ($newStatusId == $oldStatusId) {
            return;
        }

        $this->createAuditLog([
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
