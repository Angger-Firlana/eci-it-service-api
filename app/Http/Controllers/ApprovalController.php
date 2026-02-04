<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ApprovalService;

class ApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function approveVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->approveVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request approved successfully');
    }
    
    public function getApproverByServiceRequestId($serviceRequestId)
    {
        $data = $this->approvalService->getApproverByServiceRequestId($serviceRequestId);
        return APIResponse::success($data, 200, 'Approver retrieved successfully');
    }

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->rejectVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest,200, 'Vendor request rejected successfully');
    }

    public function approveRequestByAdmin(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->approveRequestByAdmin($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request approved successfully');
    }

    public function rejectedRequestByAdmin(Request $request, $id)
    {
        $serviceRequest = $this->approvalService->rejectedRequestByAdmin($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request rejected successfully');
    }
}
