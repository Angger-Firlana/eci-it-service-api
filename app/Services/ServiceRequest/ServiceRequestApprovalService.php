<?php

namespace App\Services\ServiceRequest;

use App\Models\ApprovalPolicy;
use App\Models\ApprovalPolicyStep;
use App\Models\ConditionType;
use App\Models\ServiceCost;
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
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);
        
        // Ensure approvalPolicy is found
        if (!$approvalPolicy) {
            DB::rollBack();
            throw new \Exception('No approval policy found for the given service request cost.');
        }

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
                'status_id' => 15,
                'approval_policy_id' => $approvalPolicy->id,
                'approval_policy_step_id' => $approvalPolicyStep->id,
            ]);

            
        }

        $oldStatusId = $serviceRequest->status_id;
        $newStatusId = 4;

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

    public function createVendorApprovals(int $serviceRequestId,array $approvals): Collection
    {
        DB::beginTransaction();
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);
        
        foreach($approvals['approvers'] as $approverId){
            $approver = User::findOrFail($approverId);
            $approvalPolicyStep = $approvalPolicy->approval_policy_steps->where('role_id', $approver->roles->first()->id)->first();

            $approval = VendorApproval::create([
                'service_request_id' => $serviceRequestId,
                'approver_id' => $approverId,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status_id' => 15,
                'approval_policy_id' => $approvalPolicy->id,
                'approval_policy_step_id' => $approvalPolicyStep->id,
            ]);

            $oldStatusId = $serviceRequest->status_id;
            $newStatusId = 4;
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

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => 16,
        ]);

        $oldStatusId = $approval->status_id;
        $newStatusId = 16;

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $approval->service_request_id,
            'entity_type_id' => 2,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'APPROVE_VENDOR',
            'notes' => 'Vendor approval completed',
        ]);

        // Update service request status if all approvals are done
        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    public function approveRequestByAdmin($id, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        
        $serviceRequest->update([
            'status_id' => 3
        ]);
        
        $this->auditLogService->createStatusAuditLog($serviceRequest, $serviceRequest->status, $serviceRequest->status_id, 3, $data);
        

        return $serviceRequest->load(['approver', 'assigned_by', 'service_request']);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => 17,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
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

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', 15)
            ->count();
        
        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', 17)
            ->count();
        
        if ($rejectedApprovals > 0) {
            $serviceRequest->update(['status_id' => 6]); // Rejected By Above
            $this->auditLogService->createStatusAuditLog($serviceRequest, $serviceRequest->status, $serviceRequest->status_id, 6, []);
        } elseif ($pendingApprovals === 0) {
            $serviceRequest->update(['status_id' => 7]); // In Progress
        }
    }

    public function getApproverByServiceRequestId($serviceRequestId): array
    {
        $data = [];
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);

        if (!$approvalPolicy) {
            return []; // No policy found
        }

        $approvalPolicySteps = $approvalPolicy->approval_policy_steps;
        if ($approvalPolicySteps->isEmpty()) {
            return []; // No steps found
        }
        
        $roleIds = $approvalPolicySteps->pluck('role_id')->unique()->toArray();

        $approvers = User::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })->get();

        $data['approvers'] = $approvers;
        $data['approvalPolicy'] = $approvalPolicy;
        $data['approvalPolicySteps'] = $approvalPolicySteps;
        return $data;
    }
}
