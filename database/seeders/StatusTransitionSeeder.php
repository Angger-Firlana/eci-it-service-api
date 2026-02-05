<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatusTransition;
use App\Models\Status;
use App\Models\Role;
use App\Models\EntityType;
use App\Enums\ServiceRequestStatusCode;
use RuntimeException;

class StatusTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $serviceRequestEntityType = EntityType::where('code', 'SERVICE_REQUEST')->first();
        if (!$serviceRequestEntityType) {
            throw new RuntimeException('EntityType SERVICE_REQUEST not found. Run EntityTypeSeeder first.');
        }

        $getStatus = function (string $code) use ($serviceRequestEntityType): Status {
            $status = Status::where('entity_type_id', $serviceRequestEntityType->id)
                ->where('code', $code)
                ->first();

            if (!$status) {
                throw new RuntimeException("Status {$code} for SERVICE_REQUEST not found. Check StatusSeeder.");
            }

            return $status;
        };

        $getRole = function (string $name): Role {
            $role = Role::where('name', $name)->first();

            if (!$role) {
                throw new RuntimeException("Role {$name} not found. Run RoleSeeder first.");
            }

            return $role;
        };

        // Fetch statuses (must match StatusSeeder)
        $reviewInWorkshop = $getStatus(ServiceRequestStatusCode::REVIEW_IN_WORKSHOP->value);
        $repairInWorkshop = $getStatus(ServiceRequestStatusCode::REPAIR_IN_WORKSHOP->value);
        $repairInVendor = $getStatus(ServiceRequestStatusCode::REPAIR_IN_VENDOR->value);
        $waitingApprovalAbove = $getStatus(ServiceRequestStatusCode::WAITING_APPROVAL_ABOVE->value);
        $completed = $getStatus(ServiceRequestStatusCode::COMPLETED->value);
        $badAsset = $getStatus(ServiceRequestStatusCode::BAD_ASSET->value);
        $cancelled = $getStatus(ServiceRequestStatusCode::CANCELLED->value);

        // Fetch Roles
        $admin = $getRole('admin');
        $user = $getRole('user');
        $technician = $getRole('technician');

        $superiorRoles = ['supervisor', 'manager', 'director', 'ceo'];
        $superior = null;
        foreach ($superiorRoles as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $superior = $role;
                break;
            }
        }

        if (!$superior) {
            throw new RuntimeException("None of the specified superior roles found (" . implode(', ', $superiorRoles) . "). Please check RoleSeeder.");
        }

        $transitions = [
            // From REVIEW_IN_WORKSHOP (Requested)
            [
                'from' => $reviewInWorkshop->id,
                'to' => $repairInWorkshop->id,
                'code' => 'START_REPAIR_WORKSHOP',
                'description' => 'Start repair in workshop',
                'roles' => [$admin->id, $technician->id]
            ],
            [
                'from' => $reviewInWorkshop->id,
                'to' => $completed->id,
                'code' => 'COMPLETE_AFTER_REVIEW',
                'description' => 'Mark completed after review',
                'roles' => [$admin->id, $technician->id]
            ],
            [
                'from' => $reviewInWorkshop->id,
                'to' => $badAsset->id,
                'code' => 'MARK_BAD_ASSET',
                'description' => 'Mark asset as bad after review',
                'roles' => [$admin->id, $technician->id]
            ],
            [
                'from' => $reviewInWorkshop->id,
                'to' => $cancelled->id,
                'code' => 'CANCEL_REQUEST',
                'description' => 'User cancels the request',
                'roles' => [$user->id, $admin->id]
            ],

            // From WAITING_APPROVAL_ABOVE
            [
                'from' => $waitingApprovalAbove->id,
                'to' => $repairInVendor->id,
                'code' => 'APPROVE_QUOTE_ABOVE',
                'description' => 'Superior approves vendor quote',
                'roles' => [$superior->id]
            ],
            [
                'from' => $waitingApprovalAbove->id,
                'to' => $reviewInWorkshop->id,
                'code' => 'REJECT_QUOTE_ABOVE',
                'description' => 'Superior rejects quote, back to review',
                'roles' => [$superior->id]
            ],

            // From REPAIR_IN_WORKSHOP
            [
                'from' => $repairInWorkshop->id,
                'to' => $repairInVendor->id,
                'code' => 'MOVE_TO_VENDOR',
                'description' => 'Move repair to vendor',
                'roles' => [$admin->id, $technician->id]
            ],
            [
                'from' => $repairInWorkshop->id,
                'to' => $completed->id,
                'code' => 'COMPLETE_WORK',
                'description' => 'Work completed in workshop',
                'roles' => [$admin->id, $technician->id]
            ],
            [
                'from' => $repairInWorkshop->id,
                'to' => $badAsset->id,
                'code' => 'MARK_BAD_ASSET',
                'description' => 'Mark asset as bad',
                'roles' => [$admin->id, $technician->id]
            ],

            // From REPAIR_IN_VENDOR
            [
                'from' => $repairInVendor->id,
                'to' => $waitingApprovalAbove->id,
                'code' => 'REQUEST_APPROVAL_ABOVE',
                'description' => 'Submit vendor quote for approval',
                'roles' => [$admin->id]
            ],
            [
                'from' => $repairInVendor->id,
                'to' => $completed->id,
                'code' => 'COMPLETE_VENDOR_WORK',
                'description' => 'Work completed by vendor',
                'roles' => [$admin->id]
            ],
            [
                'from' => $repairInVendor->id,
                'to' => $badAsset->id,
                'code' => 'MARK_BAD_ASSET',
                'description' => 'Vendor marks asset as bad',
                'roles' => [$admin->id]
            ],
        ];

        foreach ($transitions as $t) {
            $transition = StatusTransition::firstOrCreate([
                'from_status_id' => $t['from'],
                'to_status_id' => $t['to'],
            ], [
                'code' => $t['code'],
                'description' => $t['description']
            ]);

            $transition->roles()->syncWithoutDetaching($t['roles']);
        }
    }
}
