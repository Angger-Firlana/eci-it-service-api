<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\EntityType;
use RuntimeException;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $serviceRequestEntityType = EntityType::where('code', 'SERVICE_REQUEST')->first();
        $vendorApprovalEntityType = EntityType::where('code', 'VENDOR_APPROVAL')->first();
        $invoiceEntityType = EntityType::where('code', 'INVOICE')->first();

        if (!$serviceRequestEntityType) {
            throw new RuntimeException('EntityType SERVICE_REQUEST not found. Run EntityTypeSeeder first.');
        }

        if (!$vendorApprovalEntityType) {
            throw new RuntimeException('EntityType VENDOR_APPROVAL not found. Run EntityTypeSeeder first.');
        }

        if (!$invoiceEntityType) {
            throw new RuntimeException('EntityType INVOICE not found. Run EntityTypeSeeder first.');
        }

        $statuses = [
            // Service Request Statuses
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'PENDING', 'name' => 'Pending'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_REVIEW_ADMIN', 'name' => 'In Review (Admin)'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'APPROVED_BY_ADMIN', 'name' => 'Approved by Admin'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_REVIEW_ABOVE', 'name' => 'In Review (Above)'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'APPROVED_BY_ABOVE', 'name' => 'Approved by Above'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'REJECTED_BY_ABOVE', 'name' => 'Rejected by Above'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_PROGRESS', 'name' => 'In Progress'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'COMPLETED', 'name' => 'Completed'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'REJECTED', 'name' => 'Rejected'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'CANCELLED', 'name' => 'Cancelled'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'NEED_REVISION', 'name' => 'Need Revision'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'ESCALATED', 'name' => 'Escalated'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'IN_REVIEW_VENDOR', 'name' => 'In Review (Vendor)'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => 'APPROVED_BY_VENDOR', 'name' => 'Approved by Vendor'],


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
