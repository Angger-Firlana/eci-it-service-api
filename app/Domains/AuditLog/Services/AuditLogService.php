<?php

namespace App\Domains\AuditLog\Services;

use App\Domains\AuditLog\Actions\CreateAuditLog;
use App\Domains\AuditLog\Actions\CreateStatusAuditLog;
use App\Domains\AuditLog\Actions\CreateServiceRequestAuditLog;
use App\Domains\AuditLog\Actions\GetAuditLogsForServiceRequest;
use App\Domains\AuditLog\Actions\GetTimelineForServiceRequest;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\VendorApproval;
use Illuminate\Database\Eloquent\Collection;

class AuditLogService
{
    public function __construct(
        protected CreateAuditLog $createAuditLog,
        protected CreateStatusAuditLog $createStatusAuditLog,
        protected CreateServiceRequestAuditLog $createServiceRequestAuditLog,
        protected GetAuditLogsForServiceRequest $getAuditLogsForServiceRequest,
        protected GetTimelineForServiceRequest $getTimelineForServiceRequest
    ) {
    }

    public function createAuditLog(array $data): AuditLog
    {
        return $this->createAuditLog->execute($data);
    }

    public function createStatusAuditLog(
        ServiceRequest $serviceRequest,
        Status $status,
        int $oldStatusId,
        int $newStatusId,
        array $data
    ): void {
        $this->createStatusAuditLog->execute($serviceRequest, $status, $oldStatusId, $newStatusId, $data);
    }

    public function writeAuditLogsApproval(
        VendorApproval $vendorApproval,
        int $newStatusId,
        string $action,
        ?string $Lognotes = null,
        ?int $oldStatusId = null
    ): void
    {
        $this->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $vendorApproval->id,
            'entity_type_id' => 2,
            'action' => $action,
            'notes' => $Lognotes ?? 'Vendor approval ' . $action,
            'old_status_id' => $oldStatusId ?? $vendorApproval->status_id,
            'new_status_id' => $newStatusId,
        ]);
    }

    public function writeAuditLogsServiceRequest(
        ServiceRequest $serviceRequest,
        Status $newStatus,
        string $action,
        ?string $Lognotes = null,
        ?int $oldStatusId = null
    ): void
    {
        $this->createAuditLog([
            'actor_id' => auth()->id() ?? $serviceRequest->user_id,
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'action' => $action,
            'notes' => $Lognotes ?? "Status {$serviceRequest->status->name} to {$newStatus->name}",
            'old_status_id' => $oldStatusId ?? $serviceRequest->status_id,
            'new_status_id' => $newStatus->id,
        ]);
    }

    public function getAuditLogsForServiceRequest(ServiceRequest $serviceRequest): Collection
    {
        return $this->getAuditLogsForServiceRequest->execute($serviceRequest);
    }

    public function getTimelineForServiceRequest($auditLogs, ServiceRequest $serviceRequest): array
    {
        return $this->getTimelineForServiceRequest->execute($auditLogs, $serviceRequest);
    }
}
