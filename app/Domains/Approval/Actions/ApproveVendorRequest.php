<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ServiceRequest;
use App\Models\VendorApproval;
use App\Domains\AuditLog\Services\AuditLogService;

class ApproveVendorRequest
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
        $newStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::APPROVED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->auditLogService->writeAuditLogsApproval($approval, $newStatusId, "APPROVED", $data['notes'] ?? $approval->notes);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $vendorPendingStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $vendorRejectedStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);
        $repairInVendorStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_VENDOR);
        $cancelledStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::CANCELLED);
        $repairInWorkshopStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_WORKSHOP);
        $badAssetStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::BAD_ASSET);

        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorPendingStatusId)
            ->count();

        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorRejectedStatusId)
            ->count();

        if ($pendingApprovals === 0) {
            $oldStatusId = (int) $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $repairInVendorStatusId]);

            $this->auditLogService->writeAuditLogsServiceRequest($serviceRequest, $repairInVendorStatusId, "APPROVED", "Semua atasan sudah menyetujui");
        }
    }
}
