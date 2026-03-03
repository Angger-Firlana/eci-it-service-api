<?php

namespace App\Domains\ServiceRequest\Services;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestOperator;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestStatus;
use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;
use App\Domains\ServiceRequest\Support\LoadRelations;
use App\Domains\ServiceRequest\Actions\WriteAuditLogs;
use App\Domains\ServiceRequest\DTOs\UpdateServiceRequestData;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestDetails;

use App\Models\Status;
use App\Models\Role;
use App\Models\ServiceRequest;

class UpdateServiceRequestWorkflow
{
    protected UpdateServiceRequestStatus $updateServiceRequestStatus;
    protected UpdateServiceRequestOperator $updateServiceRequestOperator;
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;
    protected LoadRelations $loadRelations;
    protected WriteAuditLogs $writeAuditLogs;
    protected UpdateServiceRequestDetails $updateServiceRequestDetails;

    public function __construct(
        UpdateServiceRequestStatus $updateServiceRequestStatus,
        UpdateServiceRequestOperator $updateServiceRequestOperator,
        EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest,
        LoadRelations $loadRelations,
        WriteAuditLogs $writeAuditLogs,
        UpdateServiceRequestDetails $updateServiceRequestDetails
    ) {
        $this->updateServiceRequestStatus = $updateServiceRequestStatus;
        $this->updateServiceRequestOperator = $updateServiceRequestOperator;
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
        $this->loadRelations = $loadRelations;
        $this->writeAuditLogs = $writeAuditLogs;
        $this->updateServiceRequestDetails = $updateServiceRequestDetails;
    }

    public function execute($id, UpdateServiceRequestData $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $details = $data->details ?? [];
        $newStatusId = $data->statusId ?? $serviceRequest->status_id;
        $operatorId = $data->operatorId;
        $logNotes = $data->logNotes;
        
        // Ensure device is not active in other request
        if(!empty($details)){
            $this->ensureDeviceIsNotActiveInOtherRequest->execute($details, $serviceRequest->id);
            $this->updateServiceRequestDetails->execute($serviceRequest, $details);
        }

        // Update operator if provided
        if (auth()->user()->roles->contains('id', Role::OPERATOR)) {
            $this->updateServiceRequestOperator->execute($serviceRequest, auth()->user()->id);
        }

        // Update status
        $this->updateServiceRequestStatus->execute($serviceRequest, $newStatusId, $logNotes);

        return $this->loadRelations->execute($serviceRequest);
    }
}