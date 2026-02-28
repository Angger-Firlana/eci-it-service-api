<?php

namespace App\Domains\ServiceRequest\Services;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestOperator;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestStatus;
use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;
use App\Services\ServiceRequest\ServiceRequestService;

use App\Domains\ServiceRequest\DTOs\UpdateServiceRequestData;
use App\Models\Role;
use App\Models\ServiceRequest;

class UpdateServiceRequestWorkflow
{
    protected UpdateServiceRequestStatus $updateServiceRequestStatus;
    protected UpdateServiceRequestOperator $updateServiceRequestOperator;
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;
    protected ServiceRequestService $ServiceRequestService;

    public function __construct(
        UpdateServiceRequestStatus $updateServiceRequestStatus,
        UpdateServiceRequestOperator $updateServiceRequestOperator,
        EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest,
        ServiceRequestService $ServiceRequestService
    ) {
        $this->updateServiceRequestStatus = $updateServiceRequestStatus;
        $this->updateServiceRequestOperator = $updateServiceRequestOperator;
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
        $this->ServiceRequestService = $ServiceRequestService;
    }

    public function execute($id, UpdateServiceRequestData $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $details = $data->details;
        $newStatusId = $data->statusId;
        $operatorId = $data->operatorId;
        $logNotes = $data->logNotes;

        // Ensure device is not active in other request
        if(!empty($details)){
            $this->ensureDeviceIsNotActiveInOtherRequest->execute($details, $serviceRequest->id);
            $this->ServiceRequestService->syncDetails($serviceRequest, $details);   
        }

        // Update operator if provided
        if (auth()->user()->roles->contains('id', Role::OPERATOR)) {
            $this->updateServiceRequestOperator->execute($serviceRequest, auth()->user()->id);
        }

        // Update status
        $this->updateServiceRequestStatus->execute($serviceRequest, $newStatusId, $logNotes);

        return $this->ServiceRequestService->loadRelations($serviceRequest);
    }
}