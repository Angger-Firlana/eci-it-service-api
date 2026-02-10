<?php

namespace App\Services\Approval;

use App\Models\ApprovalPolicy;
use App\Models\ApprovalPolicyStep;
use App\Models\ConditionType;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ApprovalPolicyService
{
    public function createApprovalPolicy(array $data): ApprovalPolicy
    {
        return ApprovalPolicy::create($data);
    }

    public function updateApprovalPolicy(int $id, array $data): ApprovalPolicy
    {
        $approvalPolicy = ApprovalPolicy::findOrFail($id);
        $approvalPolicy->update($data);
        return $approvalPolicy;
    }

    public function deleteApprovalPolicy(int $id): void
    {
        $approvalPolicy = ApprovalPolicy::findOrFail($id);
        $approvalPolicy->delete();
    }

    public function getApprovalPolicyByServiceRequestCost($serviceCost): ?ApprovalPolicy{
        $costRangeConditionType = ConditionType::where('code', 'COST_RANGE')->first();
        if (!$costRangeConditionType) {
            return null; // No condition type found
        }
        
        $conditionValue = $serviceCost > 1000000 ? '>1000000' : '<1000000';

        $approvalPolicy = ApprovalPolicy::with(['approval_policy_steps', 'approval_policy_steps.role'])
                                ->where('condition_type_id', $costRangeConditionType->id)
                                ->where('condition_value', $conditionValue)
                                ->where('is_active', true)
                                ->first();

        return $approvalPolicy;
    } 

    public function getApprovalPolicyById(int $id): ApprovalPolicy
    {
        $approvalPolicy = ApprovalPolicy::with(['approval_policy_steps', 'approval_policy_steps.role'])
            ->findOrFail($id);

        return $approvalPolicy;
    }
}
