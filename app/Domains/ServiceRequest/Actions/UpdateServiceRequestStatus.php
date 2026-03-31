<?php

namespace App\Domains\ServiceRequest\Actions;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Domains\ServiceRequest\Actions\WriteAuditLogs;

class UpdateServiceRequestStatus
{
    protected WriteAuditLogs $writeAuditLogs;

    public function __construct(
        WriteAuditLogs $writeAuditLogs
    ){
        $this->writeAuditLogs = $writeAuditLogs;
    }

    public function execute(ServiceRequest $serviceRequest, int $newStatusId, ?string $Lognotes = null): void
    {
        $newStatus = Status::findOrFail($newStatusId);

        $this->writeAuditLogs->execute($serviceRequest, $newStatus, "UPDATE_STATUS", $Lognotes);

        $serviceRequest->update(['status_id' => $newStatusId]);
    }
}
