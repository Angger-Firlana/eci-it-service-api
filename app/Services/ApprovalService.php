<?php

use App\Models\ServiceRequest;
use App\Models\VendorApproval;
use App\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Services\AuditLogService;
 
class ApprovalService{

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);

        $oldStatusId = $approval->status_id;
        $newStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::APPROVED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
        ]);

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

    public function deviceNoNeedRepair(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => $this->getVendorApprovalStatusId(VendorApprovalStatusCode::COMPLETED),
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $vendorRejectedStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);
        $cancelledStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::CANCELLED);
        $repairInVendor = $this->getServiceRequestStatusId(ServiceRequestStatusCode::APPROVED_BY_ABOVE);
        $badAsseStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::BAD_ASSET);

        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorPendingStatusId)
            ->count();
        
        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorRejectedStatusId)
            ->count();
        
        if ($rejectedApprovals > 0) {
            // Any rejection -> REJECTED_BY_ABOVE (6)
            $oldStatusId = $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $badAsseStatusId]);
            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $badAsseStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Request ditolak oleh atasan',
            ]);
        } elseif ($pendingApprovals === 0) {
            // All approved -> APPROVED_BY_ABOVE (5) -> IN_PROGRESS (7)
            $oldStatusId = $serviceRequest->status_id;
            
            // First transition to APPROVED_BY_ABOVE
            $serviceRequest->update(['status_id' => $repairInVendor]);
            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $repairInVendor,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Semua atasan sudah menyetujui',
            ]);
        }
    }

    public function deviceNeedRepair($id, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $oldStatusId = $serviceRequest->status_id;
        $newStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_WORKSHOP);

        $serviceRequest->update([
            'status_id' => $newStatusId
        ]);

        $this->auditLogService->createStatusAuditLog($serviceRequest, $serviceRequest->status, $oldStatusId, $newStatusId, $data);
        

        return $serviceRequest->load(['approver', 'assigned_by', 'service_request']);
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