<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ServiceRequest;
use App\Models\Status;
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

        $this->auditLogService->writeAuditLogsApproval(
            $approval,
            $newStatusId,
            'APPROVED',
            $data['notes'] ?? $approval->notes,
            $oldStatusId
        );

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $vendorPendingStatusId = $this->statusResolver->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $repairInVendorStatus = Status::forEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::REPAIR_IN_VENDOR);

        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorPendingStatusId)
            ->count();

        if ($pendingApprovals === 0) {
            $oldStatusId = (int) $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $repairInVendorStatus->id]);

            $this->auditLogService->writeAuditLogsServiceRequest(
                $serviceRequest,
                $repairInVendorStatus,
                'APPROVED',
                'Semua atasan sudah menyetujui',
                $oldStatusId
            );
        }
    }
}
