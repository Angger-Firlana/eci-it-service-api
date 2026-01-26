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

class ServiceRequestApprovalService
{
    public function updateVendorApprovals(int $serviceRequestId, array $approvals): \Illuminate\Database\Eloquent\Collection
    {
        DB::beginTransaction();
        
        foreach ($approvals as $approvalData) {
            VendorApproval::updateOrCreate(
                [
                    'service_request_id' => $serviceRequestId,
                    'approver_id' => $approvalData['approver_id'],
                ],
                [
                    'assigned_by' => $approvalData['assigned_by'] ?? auth()->id(),
                    'assigned_at' => now(),
                    'approved_at' => $approvalData['approved_at'] ?? null,
                    'status_id' => $approvalData['status_id'] ?? 6,
                    'notes' => $approvalData['notes'] ?? null,
                    'approval_policy_id' => $approvalData['approval_policy_id'] ?? null,
                    'approval_policy_step_id' => $approvalData['approval_policy_step_id'] ?? null,
                ]
            );
        }
        
        DB::commit();

        return VendorApproval::where('service_request_id', $serviceRequestId)->with(['approver', 'assigned_by'])->get();
    }

    public function createVendorApprovals(int $serviceRequestId,array $approvals): Collection
    {
        DB::beginTransaction();
        
        foreach($approvals as $data){
            $approval = VendorApproval::create([
                'service_request_id' => $serviceRequestId,
                'approver_id' => $data['approver_id'],
                'assigned_by' => $data['assigned_by'] ?? auth()->id(),
                'assigned_at' => now(),
                'approved_at' => $data['approved_at'] ?? null,
                'status_id' => $data['status_id'] ?? 15,
                'notes' => $data['notes'] ?? null,
                'approval_policy_id' => $data['approval_policy_id'] ?? null,
                'approval_policy_step_id' => $data['approval_policy_step_id'] ?? null,
            ]);
        }
       
        DB::commit();

        return VendorApproval::where('service_request_id', $serviceRequestId)->with(['approver', 'assigned_by'])->get();
    }

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => 15,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $approval->service_request_id,
            'entity_type_id' => 1,
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
        } elseif ($pendingApprovals === 0) {
            $serviceRequest->update(['status_id' => 7]); // In Progress
        }
    }

    public function getApproverByServiceRequestId($serviceRequestId)
    {
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        
        $costRangeConditionType = ConditionType::where('code', 'COST_RANGE')->first();
        if (!$costRangeConditionType) {
            return collect(); // No condition type found
        }
        
        $conditionValue = $serviceCost > 1000000 ? '>1000000' : '<1000000';

        $approvalPolicy = ApprovalPolicy::where('condition_type_id', $costRangeConditionType->id)
                                ->where('condition_value', $conditionValue)
                                ->where('is_active', true)
                                ->first();

        if (!$approvalPolicy) {
            return collect(); // No policy found
        }

        $approvalPolicySteps = ApprovalPolicyStep::where('approval_policy_id', $approvalPolicy->id)->get();
        if ($approvalPolicySteps->isEmpty()) {
            return collect(); // No steps found
        }
        
        $roleIds = $approvalPolicySteps->pluck('role_id')->unique()->toArray();

        $approvers = User::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })->get();

        return $approvers;
    }
}