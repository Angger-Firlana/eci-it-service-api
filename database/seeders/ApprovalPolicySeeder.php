<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalPolicy;
use App\Models\ApprovalPolicyStep;
use App\Models\EntityType;
use App\Models\ConditionType;
use App\Models\Role;

class ApprovalPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get common entity types, condition types, and roles
        $serviceRequestEntityType = EntityType::where('code', 'SERVICE_REQUEST')->first();

        $deviceTypeConditionType = ConditionType::where('code', 'DEVICE_TYPE')->first();
        $serviceTypeConditionType = ConditionType::where('code', 'SERVICE_TYPE')->first();
        $costRangeConditionType = ConditionType::where('code', 'COST_RANGE')->first();

        $adminRole = Role::where('name', 'admin')->first();
        $superiorRole = Role::where('name', 'superior')->first();
        $technicianRole = Role::where('name', 'technician')->first();
        $userRole = Role::where('name', 'user')->first();


        if ($serviceRequestEntityType && $deviceTypeConditionType && $adminRole && $superiorRole && $technicianRole) {
            // Policy 1: Approval for Service Request based on Device Type 'Laptop'
            $policy1 = ApprovalPolicy::firstOrCreate(
                [
                    'entity_type_id' => $serviceRequestEntityType->id,
                    'condition_type_id' => $deviceTypeConditionType->id,
                    'condition_value' => 'Laptop',
                ],
                [
                    'is_active' => true,
                ]
            );

            // Steps for Policy 1
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy1->id, 'step_order' => 1],
                ['role_id' => $technicianRole->id, 'is_mandatory' => true]
            );
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy1->id, 'step_order' => 2],
                ['role_id' => $superiorRole->id, 'is_mandatory' => true]
            );

            // Policy 2: Approval for Service Request based on Service Type 'Software Installation'
            $policy2 = ApprovalPolicy::firstOrCreate(
                [
                    'entity_type_id' => $serviceRequestEntityType->id,
                    'condition_type_id' => $serviceTypeConditionType->id,
                    'condition_value' => 'Software Installation',
                ],
                [
                    'is_active' => true,
                ]
            );

            // Steps for Policy 2
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy2->id, 'step_order' => 1],
                ['role_id' => $technicianRole->id, 'is_mandatory' => true]
            );

            // Policy 3: Approval for Service Request based on Cost Range '>1000000'
            $policy3 = ApprovalPolicy::firstOrCreate(
                [
                    'entity_type_id' => $serviceRequestEntityType->id,
                    'condition_type_id' => $costRangeConditionType->id,
                    'condition_value' => '>1000000', // Example: Cost greater than 1,000,000
                ],
                [
                    'is_active' => true,
                ]
            );

            // Steps for Policy 3
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy3->id, 'step_order' => 1],
                ['role_id' => $technicianRole->id, 'is_mandatory' => true]
            );
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy3->id, 'step_order' => 2],
                ['role_id' => $superiorRole->id, 'is_mandatory' => true]
            );
            ApprovalPolicyStep::firstOrCreate(
                ['approval_policy_id' => $policy3->id, 'step_order' => 3],
                ['role_id' => $adminRole->id, 'is_mandatory' => true]
            );
        }
    }
}
