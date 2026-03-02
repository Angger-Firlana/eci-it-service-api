<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\ApproveVendorRequest;
use App\Domains\Approval\Actions\RejectVendorRequest;
use App\Domains\Approval\Actions\DeviceNeedRepair;
use App\Domains\Approval\Actions\DeviceNoNeedRepair;
use App\Domains\Approval\Actions\GetApproverByServiceRequestId;
use App\Models\ServiceRequest;
use App\Models\VendorApproval;

class ApprovalWorkflowService
{
    public function __construct(
        protected ApproveVendorRequest $approveVendorRequest,
        protected RejectVendorRequest $rejectVendorRequest,
        protected DeviceNeedRepair $deviceNeedRepair,
        protected DeviceNoNeedRepair $deviceNoNeedRepair,
        protected GetApproverByServiceRequestId $getApproverByServiceRequestId
    ) {
    }

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        return $this->approveVendorRequest->execute($approvalId, $data);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        return $this->rejectVendorRequest->execute($approvalId, $data);
    }

    public function deviceNeedRepair(int $serviceRequestId, array $data): ServiceRequest
    {
        return $this->deviceNeedRepair->execute($serviceRequestId, $data);
    }

    public function deviceNoNeedRepair(int $serviceRequestId, array $data): ServiceRequest
    {
        return $this->deviceNoNeedRepair->execute($serviceRequestId, $data);
    }

    public function getApproverByServiceRequestId(int $serviceRequestId): array
    {
        return $this->getApproverByServiceRequestId->execute($serviceRequestId);
    }
}
