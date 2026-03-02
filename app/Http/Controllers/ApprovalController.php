<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Approval\Services\ApproveVendorRequestWorkflow;
use App\Domains\Approval\Services\RejectVendorRequestWorkflow;
use App\Domains\Approval\Services\DeviceNeedRepairWorkflow;
use App\Domains\Approval\Services\DeviceNoNeedRepairWorkflow;
use App\Domains\Approval\Services\GetApproverByServiceRequestWorkflow;

class ApprovalController extends Controller
{
    protected ApproveVendorRequestWorkflow $approveVendorRequestWorkflow;
    protected RejectVendorRequestWorkflow $rejectVendorRequestWorkflow;
    protected DeviceNeedRepairWorkflow $deviceNeedRepairWorkflow;
    protected DeviceNoNeedRepairWorkflow $deviceNoNeedRepairWorkflow;
    protected GetApproverByServiceRequestWorkflow $getApproverByServiceRequestWorkflow;

    public function __construct(
        ApproveVendorRequestWorkflow $approveVendorRequestWorkflow,
        RejectVendorRequestWorkflow $rejectVendorRequestWorkflow,
        DeviceNeedRepairWorkflow $deviceNeedRepairWorkflow,
        DeviceNoNeedRepairWorkflow $deviceNoNeedRepairWorkflow,
        GetApproverByServiceRequestWorkflow $getApproverByServiceRequestWorkflow
    )
    {
        $this->approveVendorRequestWorkflow = $approveVendorRequestWorkflow;
        $this->rejectVendorRequestWorkflow = $rejectVendorRequestWorkflow;
        $this->deviceNeedRepairWorkflow = $deviceNeedRepairWorkflow;
        $this->deviceNoNeedRepairWorkflow = $deviceNoNeedRepairWorkflow;
        $this->getApproverByServiceRequestWorkflow = $getApproverByServiceRequestWorkflow;
    }

    public function approveVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approveVendorRequestWorkflow->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request approved successfully');
    }
    
    public function getApproversByServiceRequestId($serviceRequestId)
    {
        $data = $this->getApproverByServiceRequestWorkflow->execute((int) $serviceRequestId);
        return APIResponse::success($data, 200, 'Approver retrieved successfully');
    }

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->rejectVendorRequestWorkflow->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request rejected successfully');
    }

    public function deviceNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->deviceNeedRepairWorkflow->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request approved successfully');
    }

    public function deviceNoNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->deviceNoNeedRepairWorkflow->execute((int) $id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request rejected successfully');
    }
}
