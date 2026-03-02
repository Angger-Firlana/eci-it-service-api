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

    public function createServiceRequestAuditLog(ServiceRequest $serviceRequest, string $action, string $notes): void
    {
        $this->createServiceRequestAuditLog->execute($serviceRequest, $action, $notes);
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
