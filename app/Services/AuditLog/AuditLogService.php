<?php

namespace App\Services\AuditLog;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Services\ServiceRequest\ServiceRequestApprovalService;

class AuditLogService
{
    protected $serviceRequestApprovalService;

    public function __construct(ServiceRequestApprovalService $serviceRequestApprovalService)
    {
        $this->serviceRequestApprovalService = $serviceRequestApprovalService;
    }

    public function createAuditLog(array $data): AuditLog
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

    public function createServiceRequestAuditLog(ServiceRequest $serviceRequest, string $action, string $notes): void
    {
        $this->createAuditLog([
            'actor_id' => auth()->id() ?? $serviceRequest->user_id,
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'action' => $action,
            'notes' => $notes,
            'old_status_id' => $serviceRequest->status_id,
            'new_status_id' => $serviceRequest->status_id,
        ]);
    }


    public function getAuditLogsForServiceRequest(ServiceRequest $serviceRequest): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where(function ($query) use ($serviceRequest) {
            $query->where(function ($q) use ($serviceRequest) {
                $q->where('entity_type_id', 1)
                ->where('entity_id', $serviceRequest->id);
            })->orWhere(function ($q) use ($serviceRequest) {
                $q->where('entity_type_id', 2)
                ->whereIn(
                    'entity_id',
                    $this->serviceRequestApprovalService
                        ->getIdsByServiceRequestId($serviceRequest->id)
                );
            });
        })
        ->with([
            'actor:id,name',
            'actor.departments:id,name'
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getTimeLineForServiceRequest($auditLogs, ServiceRequest $serviceRequest): array
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
