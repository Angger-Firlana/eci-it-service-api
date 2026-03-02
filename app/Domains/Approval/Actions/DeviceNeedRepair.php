<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Support\ApprovalStatusResolver;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\ServiceRequest;
use App\Domains\AuditLog\Services\AuditLogService;
use App\Services\Invoice\InvoiceService;

class DeviceNeedRepair
{
    public function __construct(
        protected ApprovalStatusResolver $statusResolver,
        protected AuditLogService $auditLogService,
        protected InvoiceService $invoiceService
    ) {
    }

    public function execute(int $serviceRequestId, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);

        $oldStatusId = (int) $serviceRequest->status_id;
        $newStatusId = $this->statusResolver->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_WORKSHOP);

        $serviceRequest->update([
            'operator_id' => auth()->id(),
            'status_id' => $newStatusId,
        ]);

        $this->invoiceService->createInvoiceForServiceRequest($serviceRequest);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'UPDATE_STATUS',
            'notes' => $data['notes'] ?? 'Request disetujui untuk perbaikan di workshop',
        ]);

        return $serviceRequest->load(['status', 'user', 'operator', 'service_request_details']);
    }
}
