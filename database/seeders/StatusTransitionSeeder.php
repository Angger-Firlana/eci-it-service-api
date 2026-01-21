<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatusTransition;
use App\Models\Status;
use App\Models\Role;
use App\Models\EntityType;

class StatusTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $serviceRequestEntityType = EntityType::where('name', 'Service Request')->first();
        
        // Fetch statuses
        $pending = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'PENDING')->first();
        $inReview = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'IN_REVIEW')->first();
        $approved = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'APPROVED')->first();
        $rejected = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'REJECTED')->first();
        $inProgress = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'IN_PROGRESS')->first();
        $completed = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'COMPLETED')->first();
        $cancelled = Status::where('entity_type_id', $serviceRequestEntityType->id)->where('code', 'CANCELLED')->first();

        // Fetch Roles
        $admin = Role::where('name', 'admin')->first();
        $user = Role::where('name', 'user')->first();
        $technician = Role::where('name', 'technician')->first();

        $transitions = [
            // From PENDING
            [
                'from' => $pending->id,
                'to' => $inReview->id,
                'code' => 'REVIEW_REQUEST',
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

            // From IN_REVIEW
            [
                'from' => $inReview->id,
                'to' => $approved->id,
                'code' => 'APPROVE_REQUEST',
                'description' => 'Admin approves the request to proceed',
                'roles' => [$admin->id]
            ],
            [
                'from' => $inReview->id,
                'to' => $rejected->id,
                'code' => 'REJECT_IN_REVIEW',
                'description' => 'Admin rejects after review',
                'roles' => [$admin->id]
            ],

            // From APPROVED (Waiting for Technician/Vendor)
            [
                'from' => $approved->id,
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
