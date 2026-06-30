<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\Notification\Actions\QueueStatusChangeNotificationEmail;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\VendorApproval;
use App\Domains\AuditLog\Services\AuditLogService;

class RejectVendorRequest
{
    public function __construct(
        protected ApprovalStatusResolver $statusResolver,
        protected AuditLogService $auditLogService,
        protected QueueStatusChangeNotificationEmail $queueStatusChangeNotificationEmail,
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

        $this->auditLogService->writeAuditLogsApproval(
            $approval,
            $newStatusId,
            'REJECTED',
            $data['notes'] ?? $approval->notes,
            $oldStatusId
        );

        $this->updateServiceRequestOnRejection($approval->service_request, $data['notes'] ?? null);
        

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function updateServiceRequestOnRejection(ServiceRequest $serviceRequest, ?string $notes = null): void
    {
        $oldStatusId = (int) $serviceRequest->status_id;
        $rejectedByAboveStatus = Status::forEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::REJECTED_BY_ABOVE);
        $serviceRequest->update(['status_id' => $rejectedByAboveStatus->id]);

        $this->auditLogService->writeAuditLogsServiceRequest(
            $serviceRequest,
            $rejectedByAboveStatus,
            'REJECTED',
            $notes ?? 'Salah satu atasan menolak permintaan, status request ditolak',
            $oldStatusId
        );

        $this->queueStatusChangeNotificationEmail->execute(
            $serviceRequest,
            ServiceRequestStatusCode::REJECTED_BY_ABOVE->value,
            $notes
        );
    }
}
