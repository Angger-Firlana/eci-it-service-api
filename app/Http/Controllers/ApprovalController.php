<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Approval\Actions\ApproveVendorRequest;
use App\Domains\Approval\Actions\RejectVendorRequest;
use App\Domains\Approval\Actions\DeviceNeedRepair;
use App\Domains\Approval\Actions\DeviceNoNeedRepair;
use App\Domains\Approval\Actions\GetApproverByServiceRequestId;

class ApprovalController extends Controller
{
    protected ApproveVendorRequest $approveVendorRequest;
    protected RejectVendorRequest $rejectVendorRequest;
    protected DeviceNeedRepair $deviceNeedRepair;
    protected DeviceNoNeedRepair $deviceNoNeedRepair;
    protected GetApproverByServiceRequestId $getApproverByServiceRequestId;

    public function __construct(
        ApproveVendorRequest $approveVendorRequest,
        RejectVendorRequest $rejectVendorRequest,
        DeviceNeedRepair $deviceNeedRepair,
        DeviceNoNeedRepair $deviceNoNeedRepair,
        GetApproverByServiceRequestId $getApproverByServiceRequestId
    )
    {
        $this->approveVendorRequest = $approveVendorRequest;
        $this->rejectVendorRequest = $rejectVendorRequest;
        $this->deviceNeedRepair = $deviceNeedRepair;
        $this->deviceNoNeedRepair = $deviceNoNeedRepair;
        $this->getApproverByServiceRequestId = $getApproverByServiceRequestId;
    }

    public function approveVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approveVendorRequest->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request approved successfully');
    }
    
    public function getApproversByServiceRequestId($serviceRequestId)
    {
        $data = $this->getApproverByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($data, 200, 'Approver retrieved successfully');
    }

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->rejectVendorRequest->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request rejected successfully');
    }

    public function deviceNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->deviceNeedRepair->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request approved successfully');
    }

    public function deviceNoNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->deviceNoNeedRepair->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request rejected successfully');
    }
}
