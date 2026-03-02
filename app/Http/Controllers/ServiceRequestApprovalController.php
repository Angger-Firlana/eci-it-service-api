<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\ServiceRequestApproval\Services\ServiceRequestApprovalService;
use App\Helpers\APIResponse;
use App\Http\Requests\ServiceApprovals\UpdateApprovalsRequest;
use App\Http\Requests\ServiceApprovals\StoreApprovalsRequest;

class ServiceRequestApprovalController extends Controller
{
    //
    protected $serviceRequestApprovalService;
    
    public function __construct(ServiceRequestApprovalService $serviceRequestApprovalService)
    {
        $this->serviceRequestApprovalService = $serviceRequestApprovalService;
    }

    public function getApproverByServiceRequestId($serviceRequestId)
    {
        $data = $this->serviceRequestApprovalService->getApproverByServiceRequestId($serviceRequestId);
        return APIResponse::success($data, 200, 'Approvers retrieved successfully');
    }

    public function index($serviceRequestId)
    {
        return $this->getByServiceRequestId($serviceRequestId);
    }

    public function store($serviceRequestId,StoreApprovalsRequest $request)
    {
        $data = $this->serviceRequestApprovalService->createVendorApprovals($serviceRequestId, $request->validated());
        return APIResponse::success($data, 200, 'Service request approval created successfully');
    }

    public function getByServiceRequestId($serviceRequestId)
    {
        $data = $this->serviceRequestApprovalService->getByServiceRequestId($serviceRequestId);
        return APIResponse::success($data, 200, 'Service request approvals retrieved successfully');
    }

    public function update($serviceRequestId, UpdateApprovalsRequest $request)
    {
        $data = $this->serviceRequestApprovalService->update($serviceRequestId, $request->validated());
        return APIResponse::success($data, 200, 'Service request approval updated successfully');
    }

    public function destroy($serviceRequestId, $approvalId)
    {
        $data = $this->serviceRequestApprovalService->destroy($serviceRequestId, $approvalId);
        return APIResponse::success($data, 200, 'Service request approval deleted successfully');
    }
}
