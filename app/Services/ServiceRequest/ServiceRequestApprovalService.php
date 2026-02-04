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
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
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

    public function createVendorApprovals(int $serviceRequestId,array $approvals): Collection
    {
        DB::beginTransaction();
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

    public function approveRequestByAdmin($id, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $oldStatusId = $serviceRequest->status_id;
        $newStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::APPROVED_BY_ADMIN);

        $serviceRequest->update([
            'status_id' => $newStatusId
        ]);

        $this->auditLogService->createStatusAuditLog($serviceRequest, $serviceRequest->status, $oldStatusId, $newStatusId, $data);
        

        return $serviceRequest->load(['approver', 'assigned_by', 'service_request']);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => $this->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED),
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
        $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $vendorRejectedStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);
        $rejectedByAboveStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::REJECTED_BY_ABOVE);
        $approvedByAboveStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::APPROVED_BY_ABOVE);
        $inProgressStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::IN_PROGRESS);

        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorPendingStatusId)
            ->count();
        
        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorRejectedStatusId)
            ->count();
        
        if ($rejectedApprovals > 0) {
            // Any rejection -> REJECTED_BY_ABOVE (6)
            $oldStatusId = $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $rejectedByAboveStatusId]);
            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $rejectedByAboveStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Request ditolak oleh atasan',
            ]);
        } elseif ($pendingApprovals === 0) {
            // All approved -> APPROVED_BY_ABOVE (5) -> IN_PROGRESS (7)
            $oldStatusId = $serviceRequest->status_id;
            
            // First transition to APPROVED_BY_ABOVE
            $serviceRequest->update(['status_id' => $approvedByAboveStatusId]);
            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $approvedByAboveStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Semua atasan sudah menyetujui',
            ]);
            
            // Auto-transition to IN_PROGRESS
            $serviceRequest->update(['status_id' => $inProgressStatusId]);
            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $approvedByAboveStatusId,
                'new_status_id' => $inProgressStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Service dimulai (auto-transition)',
            ]);
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

    private function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }

    private function getVendorApprovalStatusId(VendorApprovalStatusCode|string $code): int
    {
        return Status::idForEntityCode('VENDOR_APPROVAL', $code);
    }
}
