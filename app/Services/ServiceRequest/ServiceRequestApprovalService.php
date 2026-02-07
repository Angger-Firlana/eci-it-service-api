<?php

namespace App\Services\ServiceRequest;

use App\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ApprovalPolicy;
use App\Models\ApprovalPolicyStep;
use App\Models\ConditionType;
use App\Models\ServiceCost;
use App\Models\Status;
use App\Models\User;
use App\Models\VendorApproval;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Services\ApprovalPolicyService;
use App\Services\AuditLogService;

class ServiceRequestApprovalService
{
    protected $approvalPolicyService;
    protected $auditLogService;
    
    public function __construct(ApprovalPolicyService $approvalPolicyService, AuditLogService $auditLogService)
    {
        $this->approvalPolicyService = $approvalPolicyService;
        $this->auditLogService = $auditLogService;
    }
    
    public function update(int $serviceRequestId, array $approvalsData): \Illuminate\Database\Eloquent\Collection
    {
        DB::beginTransaction();

        // 1. Delete existing VendorApproval records for this service request
        VendorApproval::where('service_request_id', $serviceRequestId)->delete();

        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $serviceCost = ServiceCost::where([
            'service_request_id' => $serviceRequestId],
            
        )->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);
        
        // Ensure approvalPolicy is found
        if (!$approvalPolicy) {
            DB::rollBack();
            throw new \Exception('No approval policy found for the given service request cost.');
        }

        $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $inReviewAboveStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::IN_REVIEW_ABOVE);

        // Use the 'approvers' key from the validated data
        foreach ($approvalsData['approvers'] as $approverId) {
            $approver = User::findOrFail($approverId);
            $approvalPolicyStep = $approvalPolicy->approval_policy_steps->where('role_id', $approver->roles->first()->id)->first();

            // Ensure approvalPolicyStep is found for the approver's role
            if (!$approvalPolicyStep) {
                DB::rollBack();
                throw new \Exception('No approval policy step found for approver role.');
            }

            VendorApproval::create([
                'service_request_id' => $serviceRequestId,
                'approver_id' => $approverId,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status_id' => $vendorPendingStatusId,
                'approval_policy_id' => $approvalPolicy->id,
                'approval_policy_step_id' => $approvalPolicyStep->id,
            ]);
        }

        $oldStatusId = $serviceRequest->status_id;
        $newStatusId = $inReviewAboveStatusId;

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequestId,
            'entity_type_id' => 1, // Assuming 1 is the entity type for VendorApproval
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'UPDATE_VENDOR_APPROVAL', // Action reflects re-creation
            'notes' => 'Vendor approvals re-created for service request ' . $serviceRequestId,
        ]);
        
        DB::commit();

        return VendorApproval::where('service_request_id', $serviceRequestId)->with(['approver', 'assigned_by'])->get();
    }

    public function createVendorApprovals(int $serviceRequestId): Collection
    {
        DB::beginTransaction();

        $approvers = $this->getApproverByServiceRequestId($serviceRequestId)['approvers'];
        $approvals = [
            'approvers' => $approvers->pluck('id')->toArray()
        ];
        
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);

        $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $inReviewAboveStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::IN_REVIEW_ABOVE);
        $oldStatusId = $serviceRequest->status_id;
        $newStatusId = $inReviewAboveStatusId;
        
        foreach($approvals['approvers'] as $approverId){
            $approver = User::findOrFail($approverId);
            $approvalPolicyStep = $approvalPolicy->approval_policy_steps->where('role_id', $approver->roles->first()->id)->first();

            $approval = VendorApproval::create([
                'service_request_id' => $serviceRequestId,
                'approver_id' => $approverId,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status_id' => $vendorPendingStatusId,
                'approval_policy_id' => $approvalPolicy->id,
                'approval_policy_step_id' => $approvalPolicyStep->id,
            ]);
        }
       
        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequestId,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'CREATE_VENDOR_APPROVAL',
            'notes' => 'Vendor approval created',
        ]);

        DB::commit();

        return VendorApproval::where('service_request_id', $serviceRequestId)->with(['approver', 'assigned_by'])->get();
    }    
    public function deleteVendorApproval(int $id): void
    {
        $approval = VendorApproval::findOrFail($id);
        $approval->delete();
    }

    public function getApprovalById(int $id): VendorApproval
    {
        $approval = VendorApproval::with(['approver', 'assigned_by', 'service_request'])
            ->findOrFail($id);

        return $approval;
    }

    public function getByServiceRequestId(int $serviceRequestId): \Illuminate\Database\Eloquent\Collection
    {
        $approvals = VendorApproval::with(['approver', 'assigned_by'])
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $approvals;
    }

   

    private function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }

    private function getVendorApprovalStatusId(VendorApprovalStatusCode|string $code): int
    {
        return Status::idForEntityCode('VENDOR_APPROVAL', $code);
    }
}
