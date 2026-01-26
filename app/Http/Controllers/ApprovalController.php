<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ServiceRequest\ServiceRequestApprovalService;

class ApprovalController extends Controller
{
    protected $serviceRequestApprovalService;

    public function __construct(ServiceRequestApprovalService $serviceRequestApprovalService)
    {
        $this->serviceRequestApprovalService = $serviceRequestApprovalService;
    }

    public function approveVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestApprovalService->approveVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Vendor request approved successfully');
    }
    
    public function getApproverByServiceRequestId($serviceRequestId)
    {
        $data = $this->serviceRequestApprovalService->getApproverByServiceRequestId($serviceRequestId);
        return APIResponse::success($data, 200, 'Approver retrieved successfully');
    }

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestApprovalService->rejectVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest,200, 'Vendor request rejected successfully');
    }

    public function approveRequestByAdmin(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestApprovalService->approveRequestByAdmin($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request approved successfully');
    }

    public function rejectedRequestByAdmin(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestApprovalService->rejectedRequestByAdmin($id, $request->all());
        return APIResponse::success($serviceRequest, 200, 'Request rejected successfully');
    }
}
