<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\EntityType;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
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
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::REVIEW_IN_WORKSHOP->value, 'name' => 'Requested'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::REPAIR_IN_WORKSHOP->value, 'name' => 'Repair in Workshop'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::REPAIR_IN_VENDOR->value, 'name' => 'Repair in Vendor'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::WAITING_APPROVAL_ABOVE->value, 'name' => 'Waiting Approval Above'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::COMPLETED->value, 'name' => 'Completed'],
            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::BAD_ASSET->value, 'name' => 'Bad Asset'],

            ['entity_type_id' => $serviceRequestEntityType->id, 'code' => ServiceRequestStatusCode::CANCELLED->value, 'name' => 'Cancelled'],



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
