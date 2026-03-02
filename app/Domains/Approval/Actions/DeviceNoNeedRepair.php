<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\ServiceRequest;
use App\Services\AuditLog\AuditLogService;

class DeviceNoNeedRepair
{
    public function __construct(
        protected ApprovalStatusResolver $statusResolver,
        protected AuditLogService $auditLogService
    ) {
    }

    public function execute(int $serviceRequestId, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);

        $oldStatusId = (int) $serviceRequest->status_id;
        $newStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::COMPLETED);

        $serviceRequest->update([
            'operator_id' => auth()->id(),
            'status_id' => $newStatusId,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'UPDATE_STATUS',
            'notes' => $data['notes'] ?? 'Device tidak memerlukan service',
        ]);

        return $serviceRequest->load(['status', 'user', 'operator', 'service_request_details']);
    }
}
