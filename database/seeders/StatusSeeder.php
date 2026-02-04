<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\EntityType;
use App\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Enums\InvoiceStatusCode;
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
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::PENDING->value, 'name' => 'Pending'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::IN_REVIEW_ADMIN->value, 'name' => 'In Review (Admin)'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::APPROVED_BY_ADMIN->value, 'name' => 'Approved by Admin'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::IN_REVIEW_ABOVE->value, 'name' => 'In Review (Above)'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::APPROVED_BY_ABOVE->value, 'name' => 'Approved by Above'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::REJECTED_BY_ABOVE->value, 'name' => 'Rejected by Above'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::IN_PROGRESS->value, 'name' => 'In Progress'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::COMPLETED->value, 'name' => 'Completed'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::REJECTED->value, 'name' => 'Rejected'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::CANCELLED->value, 'name' => 'Cancelled'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::NEED_REVISION->value, 'name' => 'Need Revision'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::ESCALATED->value, 'name' => 'Escalated'], //ga main
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::IN_REVIEW_VENDOR->value, 'name' => 'In Review (Vendor)'], //ga main
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::APPROVED_BY_VENDOR->value, 'name' => 'Approved by Vendor'], //ga main



            // Vendor Approval Statuses
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => VendorApprovalStatusCode::PENDING->value, 'name' => 'Pending'],
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => VendorApprovalStatusCode::APPROVED->value, 'name' => 'Approved'],
            ['entity_type_id' => $vendorApprovalEntityType->id, 'code' => VendorApprovalStatusCode::REJECTED->value, 'name' => 'Rejected'],
            // Invoice Statuses 
            ['entity_type_id' => $invoiceEntityType->id, 'code' => InvoiceStatusCode::DRAFT->value, 'name' => 'Draft'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => InvoiceStatusCode::SENT->value, 'name' => 'Sent'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => InvoiceStatusCode::PAID->value, 'name' => 'Paid'],
            ['entity_type_id' => $invoiceEntityType->id, 'code' => InvoiceStatusCode::OVERDUE->value, 'name' => 'Overdue'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate([
                'entity_type_id' => $status['entity_type_id'],
                'code' => $status['code']
            ], $status);
        }
    }
}
