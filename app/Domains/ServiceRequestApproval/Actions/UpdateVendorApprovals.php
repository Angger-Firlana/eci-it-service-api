<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Domains\AuditLog\Services\AuditLogService;
use App\Domains\Approval\Services\ApprovalPolicyService;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Exceptions\ApiException;
use App\Models\ServiceCost;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\User;
use App\Models\VendorApproval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UpdateVendorApprovals
{
    public function __construct(
        protected ApprovalPolicyService $approvalPolicyService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function execute(int $serviceRequestId, array $approvalsData): Collection
    {
        return DB::transaction(function () use ($serviceRequestId, $approvalsData) {
            VendorApproval::where('service_request_id', $serviceRequestId)->delete();

            $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
            $serviceCost = ServiceCost::where([
                'service_request_id' => $serviceRequestId,
            ])->sum('amount');
            $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);

            if (!$approvalPolicy) {
                throw ApiException::unprocessable('No approval policy found for the given service request cost.');
            }

            $vendorPendingStatusId = $this->getVendorApprovalStatusId(VendorApprovalStatusCode::PENDING);
            $waitingApprovalAboveStatus = Status::forEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::WAITING_APPROVAL_ABOVE);
            $oldStatusId = (int) $serviceRequest->status_id;

            foreach ($approvalsData['approvers'] as $approverId) {
                $approver = User::findOrFail($approverId);
                $approvalPolicyStep = $approvalPolicy->approval_policy_steps
                    ->where('role_id', optional($approver->roles->first())->id)
                    ->first();

                if (!$approvalPolicyStep) {
                    throw ApiException::unprocessable('No approval policy step found for approver role.');
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

            if ($serviceRequest->status_id !== $waitingApprovalAboveStatus->id) {
                $serviceRequest->update(['status_id' => $waitingApprovalAboveStatus->id]);
                $this->auditLogService->writeAuditLogsServiceRequest(
                    $serviceRequest,
                    $waitingApprovalAboveStatus,
                    'UPDATE_VENDOR_APPROVAL',
                    'Vendor approval updated',
                    $oldStatusId
                );
            }

            return VendorApproval::where('service_request_id', $serviceRequestId)
                ->with(['approver', 'assigned_by'])
                ->get();
        });
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
