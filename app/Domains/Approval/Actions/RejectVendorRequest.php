<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ServiceRequest;
use App\Models\VendorApproval;
use App\Services\AuditLog\AuditLogService;

class RejectVendorRequest
{
    public function __construct(
        protected ApprovalStatusResolver $statusResolver,
        protected AuditLogService $auditLogService
    ) {
    }

    public function execute(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);

        $oldStatusId = (int) $approval->status_id;
        $newStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $approval->id,
            'entity_type_id' => 2,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'REJECT_VENDOR',
            'notes' => $data['notes'] ?? 'Vendor approval rejected',
        ]);

        $this->updateServiceRequestOnRejection($approval->service_request, $data['notes'] ?? null);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function updateServiceRequestOnRejection(ServiceRequest $serviceRequest, ?string $notes = null): void
    {
        $oldStatusId = (int) $serviceRequest->status_id;
        $cancelledStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::CANCELLED);
        $serviceRequest->update(['status_id' => $cancelledStatusId]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $cancelledStatusId,
            'action' => 'ABOVES_REJECTED',
            'notes' => $notes ?? 'Salah satu atasan menolak permintaan, status request dibatalkan',
        ]);
    }
}
