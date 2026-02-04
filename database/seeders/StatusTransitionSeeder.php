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
        $pending = $getStatus(ServiceRequestStatusCode::PENDING->value);
        $inReviewAdmin = $getStatus(ServiceRequestStatusCode::IN_REVIEW_ADMIN->value);
        $approvedByAdmin = $getStatus(ServiceRequestStatusCode::APPROVED_BY_ADMIN->value);
        $inReviewAbove = $getStatus(ServiceRequestStatusCode::IN_REVIEW_ABOVE->value);
        $approvedByAbove = $getStatus(ServiceRequestStatusCode::APPROVED_BY_ABOVE->value);
        $inReviewVendor = $getStatus(ServiceRequestStatusCode::IN_REVIEW_VENDOR->value);
        $approvedByVendor = $getStatus(ServiceRequestStatusCode::APPROVED_BY_VENDOR->value);
        $rejected = $getStatus(ServiceRequestStatusCode::REJECTED->value);
        $cancelled = $getStatus(ServiceRequestStatusCode::CANCELLED->value);
        $inProgress = $getStatus(ServiceRequestStatusCode::IN_PROGRESS->value);
        $completed = $getStatus(ServiceRequestStatusCode::COMPLETED->value);

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
            // From PENDING
            [
                'from' => $pending->id,
                'to' => $inReviewAdmin->id,
                'code' => 'REVIEW_REQUEST_ADMIN',
                'description' => 'Admin reviews the request',
                'roles' => [$admin->id]
            ],
            [
                'from' => $pending->id,
                'to' => $rejected->id,
                'code' => 'REJECT_REQUEST',
                'description' => 'Admin rejects the request',
                'roles' => [$admin->id]
            ],
            [
                'from' => $pending->id,
                'to' => $cancelled->id,
                'code' => 'CANCEL_REQUEST',
                'description' => 'User cancels the request',
                'roles' => [$user->id, $admin->id]
            ],

            // From IN_REVIEW_ADMIN
            [
                'from' => $inReviewAdmin->id,
                'to' => $approvedByAdmin->id,
                'code' => 'APPROVE_REQUEST_ADMIN',
                'description' => 'Admin approves the request',
                'roles' => [$admin->id]
            ],
            [
                'from' => $inReviewAdmin->id,
                'to' => $rejected->id,
                'code' => 'REJECT_IN_REVIEW',
                'description' => 'Admin rejects after review',
                'roles' => [$admin->id]
            ],

            // From APPROVED_BY_ADMIN
            [
                'from' => $approvedByAdmin->id,
                'to' => $inReviewAbove->id,
                'code' => 'REVIEW_REQUEST_ABOVE',
                'description' => 'Superior reviews the request',
                'roles' => [$superior->id]
            ],

            // From IN_REVIEW_ABOVE
            [
                'from' => $inReviewAbove->id,
                'to' => $approvedByAbove->id,
                'code' => 'APPROVE_REQUEST_ABOVE',
                'description' => 'Superior approves the request',
                'roles' => [$superior->id]
            ],
            [
                'from' => $inReviewAbove->id,
                'to' => $rejected->id,
                'code' => 'REJECT_REQUEST_ABOVE',
                'description' => 'Superior rejects the request',
                'roles' => [$superior->id]
            ],

            // From APPROVED_BY_ABOVE
            [
                'from' => $approvedByAbove->id,
                'to' => $inReviewVendor->id,
                'code' => 'REVIEW_REQUEST_VENDOR',
                'description' => 'Vendor review process starts',
                'roles' => [$admin->id]
            ],

            // From IN_REVIEW_VENDOR
            [
                'from' => $inReviewVendor->id,
                'to' => $approvedByVendor->id,
                'code' => 'APPROVE_REQUEST_VENDOR',
                'description' => 'Vendor approves the request',
                'roles' => [$admin->id]
            ],
            [
                'from' => $inReviewVendor->id,
                'to' => $rejected->id,
                'code' => 'REJECT_REQUEST_VENDOR',
                'description' => 'Vendor rejects the request',
                'roles' => [$admin->id]
            ],

            // From APPROVED_BY_VENDOR
            [
                'from' => $approvedByVendor->id,
                'to' => $inProgress->id,
                'code' => 'START_WORK',
                'description' => 'Work starts on the request',
                'roles' => [$admin->id, $technician->id]
            ],

            // From IN_PROGRESS
            [
                'from' => $inProgress->id,
                'to' => $completed->id,
                'code' => 'COMPLETE_WORK',
                'description' => 'Work is completed',
                'roles' => [$admin->id, $technician->id]
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
