<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\EntityType;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $serviceRequestEntityType = EntityType::where('name', 'Service Request')->first();
        $vendorApprovalEntityType = EntityType::where('name', 'Vendor Approval')->first();
        $invoiceEntityType = EntityType::where('name', 'Invoice')->first();

        $statuses = [
            // Service Request Statuses
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'PENDING', 'name' => 'Pending'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'APPROVED', 'name' => 'Approved'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'REJECTED', 'name' => 'Rejected'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_REVIEW', 'name' => 'In Review'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_PROGRESS', 'name' => 'In Progress'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'COMPLETED', 'name' => 'Completed'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'CANCELLED', 'name' => 'Cancelled'],

            // Vendor Approval Statuses
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => 'PENDING', 'name' => 'Pending'],
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => 'APPROVED', 'name' => 'Approved'],
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => 'REJECTED', 'name' => 'Rejected'],

            // Invoice Statuses
            ['entity_type_id' => $invoiceEntityType->id, 'code' => 'DRAFT', 'name' => 'Draft'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => 'SENT', 'name' => 'Sent'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => 'PAID', 'name' => 'Paid'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => 'OVERDUE', 'name' => 'Overdue'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate([
                'entity_type_id' => $status['entity_type_id'],
                'code' => $status['code']
            ], $status);
        }
    }
}
