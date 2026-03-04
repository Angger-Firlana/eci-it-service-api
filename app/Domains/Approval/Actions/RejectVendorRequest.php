<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ServiceRequest;
use App\Models\VendorApproval;
use App\Domains\AuditLog\Services\AuditLogService;

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

        $newStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->auditLogService->writeAuditLogsApproval($approval, $newStatusId, "REJECTED", $data['notes'] ?? $approval->notes);

        $this->updateServiceRequestOnRejection($approval->service_request, $data['notes'] ?? null);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function updateServiceRequestOnRejection(ServiceRequest $serviceRequest, ?string $notes = null): void
    {
        $oldStatusId = (int) $serviceRequest->status_id;
        $rejectedByAboveId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::REJECTED_BY_ABOVE);
        $serviceRequest->update(['status_id' => $rejectedByAboveId]);

        $this->auditLogService->writeAuditLogsServiceRequest($serviceRequest, $rejectedByAboveId, "REJECTED", $notes ?? 'Salah satu atasan menolak permintaan, status request ditolak');
    }
}
