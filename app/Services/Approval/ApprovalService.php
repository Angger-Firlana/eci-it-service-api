<?php

namespace App\Services\Approval;

use App\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\Department;
use App\Models\ServiceCost;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDetail;
use App\Models\Status;
use App\Models\User;
use App\Models\VendorApproval;
use App\Services\AuditLog\AuditLogService;
use Illuminate\Support\Collection;

class ApprovalService
{
    public function __construct(
        protected ApprovalPolicyService $approvalPolicyService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);

        $oldStatusId = (int) $approval->status_id;
        $newStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::APPROVED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $approval->service_request_id,
            'entity_type_id' => 2,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'APPROVE_VENDOR',
            'notes' => $data['notes'] ?? 'Vendor approval completed',
        ]);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);

        $oldStatusId = (int) $approval->status_id;
        $newStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);

        $approval->update([
            'approved_at' => now(),
            'status_id' => $newStatusId,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $approval->service_request_id,
            'entity_type_id' => 2,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'REJECT_VENDOR',
            'notes' => $data['notes'] ?? 'Vendor approval rejected',
        ]);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    public function deviceNeedRepair(int $serviceRequestId, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);

        $oldStatusId = (int) $serviceRequest->status_id;
        $newStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_WORKSHOP);

        $serviceRequest->update([
            'status_id' => $newStatusId,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'UPDATE_STATUS',
            'notes' => $data['notes'] ?? 'Request disetujui untuk perbaikan di workshop',
        ]);

        return $serviceRequest->load(['status', 'user', 'admin', 'service_request_details']);
    }

    public function deviceNoNeedRepair(int $serviceRequestId, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);

        $oldStatusId = (int) $serviceRequest->status_id;
        $newStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::COMPLETED);

        $serviceRequest->update([
            'status_id' => $newStatusId,
        ]);

        $this->auditLogService->createAuditLog([
            'actor_id' => auth()->id(),
            'entity_id' => $serviceRequest->id,
            'entity_type_id' => 1,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action' => 'UPDATE_STATUS',
            'notes' => $data['notes'] ?? 'Device tidak memerlukan service',
        ]);

        return $serviceRequest->load(['status', 'user', 'admin', 'service_request_details']);
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
        $vendorRejectedStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::REJECTED);
        $repairInVendorStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::REPAIR_IN_VENDOR);
        $repairInWorkshopStatusId = $this->getServiceRequestStatusId(getServiceRequestStatusId::REPAIR_IN_WORKSHOP);
        $badAssetStatusId = $this->getServiceRequestStatusId(ServiceRequestStatusCode::BAD_ASSET);

        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorPendingStatusId)
            ->count();

        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', $vendorRejectedStatusId)
            ->count();

        if ($rejectedApprovals > 0) {
            $oldStatusId = (int) $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $repairInWorkshopStatusId]);

            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $badAssetStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Request ditolak oleh atasan',
            ]);
            return;
        }

        if ($pendingApprovals === 0) {
            $oldStatusId = (int) $serviceRequest->status_id;
            $serviceRequest->update(['status_id' => $repairInVendorStatusId]);

            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id(),
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $repairInVendorStatusId,
                'action' => 'STATUS_CHANGE',
                'notes' => 'Semua atasan sudah menyetujui',
            ]);
        }
    }

    public function getApproverByServiceRequestId(int $serviceRequestId): array
    {
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);
        $itDepartmentId = Department::where('code', 'IT')->first()->id;

        if (!$approvalPolicy) {
            return [
                'approvers' => [],
                'approvalPolicy' => null
            ];
        }

        $approvalPolicySteps = $approvalPolicy->approval_policy_steps;
        if ($approvalPolicySteps->isEmpty()) {
            return [
                'approvers' => [],
                'approvalPolicy' => $approvalPolicy
            ];
        }

        $roleIds = $approvalPolicy->approval_policy_steps->pluck('role_id')->filter()->unique()->values();
        
        $approvers = User::whereHas('roles', fn ($q) =>
                        $q->whereIn('roles.id', $roleIds)
                    )
                    ->whereHas('departments', fn ($q) =>
                        $q->where('departments.id', $itDepartmentId)
                    )
                    ->select('name', 'email')
                    ->orderBy('id')
                    ->get(); // ⬅️ WAJIB


        return [
            'approvers' => $approvers,
            'approvalPolicy' => $approvalPolicy
        ];
    }

    private function markDevicesAsBadAsset(int $serviceRequestId): void
    {
        $deviceIds = ServiceRequestDetail::where('service_request_id', $serviceRequestId)
            ->whereNotNull('device_id')
            ->pluck('device_id')
            ->unique()
            ->values();

        if ($deviceIds->isEmpty()) {
            return;
        }

        \App\Models\Device::whereIn('id', $deviceIds)->update(['bad_asset' => true]);
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

