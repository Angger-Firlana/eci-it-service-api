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

    public function rejectVendorRequest(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestApprovalService->rejectVendorRequest($id, $request->all());
        return APIResponse::success($serviceRequest,200, 'Vendor request rejected successfully');
    }
}
