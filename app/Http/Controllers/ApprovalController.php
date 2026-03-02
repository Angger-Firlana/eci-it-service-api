<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Approval\Services\ApprovalWorkflowService;

class ApprovalController extends Controller
{
    protected ApprovalWorkflowService $approvalService;

    public function __construct(ApprovalWorkflowService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function approveVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->approveVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request approved successfully');
    }
    
    public function getApproversByServiceRequestId($serviceRequestId)
    {
        $data = $this->approvalService->getApproverByServiceRequestId($serviceRequestId);
        return APIResponse::success($data, 200, 'Approver retrieved successfully');
    }

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->rejectVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request rejected successfully');
    }

    public function deviceNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->deviceNeedRepair($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request approved successfully');
    }

    public function deviceNoNeedRepair(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->deviceNoNeedRepair($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request rejected successfully');
    }
}
