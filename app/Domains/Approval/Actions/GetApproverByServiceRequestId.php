<?php

namespace App\Domains\Approval\Actions;

use App\Domains\Approval\Services\ApprovalPolicyService;
use App\Models\Department;
use App\Models\ServiceCost;
use App\Models\User;

class GetApproverByServiceRequestId
{
    public function __construct(
        protected ApprovalPolicyService $approvalPolicyService
    ) {
    }

    public function execute(int $serviceRequestId): array
    {
        $serviceCost = ServiceCost::where('service_request_id', $serviceRequestId)->sum('amount');
        $approvalPolicy = $this->approvalPolicyService->getApprovalPolicyByServiceRequestCost($serviceCost);
        $itDepartmentId = Department::where('code', 'IT')->first()->id;

        if (!$approvalPolicy) {
            return [
                'approvers' => [],
                'approvalPolicy' => null,
            ];
        }

        $approvalPolicySteps = $approvalPolicy->approval_policy_steps;
        if ($approvalPolicySteps->isEmpty()) {
            return [
                'approvers' => [],
                'approvalPolicy' => $approvalPolicy,
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
            ->get();

        return [
            'approvers' => $approvers,
            'approvalPolicy' => $approvalPolicy,
        ];
    }
}
