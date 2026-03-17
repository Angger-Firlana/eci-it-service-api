<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\ServiceRequestApproval\Actions\CreateVendorApprovals;
use App\Domains\ServiceRequestApproval\Actions\DeleteVendorApproval;
use App\Domains\ServiceRequestApproval\Actions\GetApproverByServiceRequestId;
use App\Domains\ServiceRequestApproval\Actions\GetVendorApprovalById;
use App\Domains\ServiceRequestApproval\Actions\ListVendorApprovalsByServiceRequestId;
use App\Domains\ServiceRequestApproval\Actions\UpdateVendorApprovals;
use App\Helpers\APIResponse;
use App\Exceptions\ApiException;
use App\Http\Requests\ServiceApprovals\UpdateApprovalsRequest;
use App\Http\Requests\ServiceApprovals\StoreApprovalsRequest;

class ServiceRequestApprovalController extends Controller
{
    protected UpdateVendorApprovals $updateVendorApprovals;
    protected CreateVendorApprovals $createVendorApprovals;
    protected DeleteVendorApproval $deleteVendorApproval;
    protected GetVendorApprovalById $getVendorApprovalById;
    protected ListVendorApprovalsByServiceRequestId $listVendorApprovalsByServiceRequestId;
    protected GetApproverByServiceRequestId $getApproverByServiceRequestId;
    
    public function __construct(
        UpdateVendorApprovals $updateVendorApprovals,
        CreateVendorApprovals $createVendorApprovals,
        DeleteVendorApproval $deleteVendorApproval,
        GetVendorApprovalById $getVendorApprovalById,
        ListVendorApprovalsByServiceRequestId $listVendorApprovalsByServiceRequestId,
        GetApproverByServiceRequestId $getApproverByServiceRequestId
    )
    {
        $this->updateVendorApprovals = $updateVendorApprovals;
        $this->createVendorApprovals = $createVendorApprovals;
        $this->deleteVendorApproval = $deleteVendorApproval;
        $this->getVendorApprovalById = $getVendorApprovalById;
        $this->listVendorApprovalsByServiceRequestId = $listVendorApprovalsByServiceRequestId;
        $this->getApproverByServiceRequestId = $getApproverByServiceRequestId;
    }

    public function getApproverByServiceRequestId($serviceRequestId)
    {
        $data = $this->getApproverByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($data, 200, 'Approvers retrieved successfully');
    }

    public function index($serviceRequestId)
    {
        return $this->getByServiceRequestId($serviceRequestId);
    }

    public function store($serviceRequestId,StoreApprovalsRequest $request)
    {
        $data = $this->createVendorApprovals->execute((int) $serviceRequestId, $request->validated());
        return APIResponse::success($data, 200, 'Service request approval created successfully');
    }

    public function getByServiceRequestId($serviceRequestId)
    {
        $data = $this->listVendorApprovalsByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($data, 200, 'Service request approvals retrieved successfully');
    }

    public function update($serviceRequestId, UpdateApprovalsRequest $request)
    {
        $data = $this->updateVendorApprovals->execute((int) $serviceRequestId, $request->validated());
        return APIResponse::success($data, 200, 'Service request approval updated successfully');
    }

    public function destroy($serviceRequestId, $approvalId)
    {
        $approval = $this->getVendorApprovalById->execute((int) $approvalId);

        if ((int) $approval->service_request_id !== (int) $serviceRequestId) {
            throw ApiException::badRequest('Approval does not belong to this service request');
        }

        $this->deleteVendorApproval->execute((int) $approvalId);
        return APIResponse::success(true, 200, 'Service request approval deleted successfully');
    }
}
