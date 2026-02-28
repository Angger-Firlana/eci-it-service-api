<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use App\Domains\ServiceRequest\Helpers\StatusHandler;

use App\Services\Approval\ApprovalPolicyService;
use App\Services\AuditLog\AuditLogService;
use App\Services\Invoice\InvoiceService;
use App\Services\Notification\NotificationService;

class UpdateServiceRequest
{
    protected ApprovalPolicyService $approvalPolicyService;
    protected AuditLogService $auditLogService;
    protected InvoiceService $invoiceService;
    protected NotificationService $notificationService;
    protected StatusHandler $statusHandler;

    public function __construct(
        ApprovalPolicyService $approvalPolicyService,
        AuditLogService $auditLogService,
        InvoiceService $invoiceService,
        NotificationService $notificationService,
        StatusHandler $statusHandler
    ) {
        $this->approvalPolicyService = $approvalPolicyService;
        $this->auditLogService = $auditLogService;
        $this->invoiceService = $invoiceService;
        $this->notificationService = $notificationService;
        $this->statusHandler = $statusHandler;
    }
    //function to update service request
    public function updateServiceRequest(int $id, array $data): ServiceRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $serviceRequest = ServiceRequest::findOrFail($id);
            $oldStatusId = $serviceRequest->status_id;

            

            $newStatusId = $data['status_id'] ?? $serviceRequest->status_id;

            $this->statusHandler->handle($serviceRequest,$status,$status->code,$this->notificationService, $this->invoiceService, $this, $data);

            return $this->loadRelations($serviceRequest);
        });
    }
}