<?php

namespace App\Domains\Approval\Support;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\Status;

class ApprovalStatusResolver
{
    public function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }

    public function getVendorApprovalStatusId(VendorApprovalStatusCode|string $code): int
    {
        return Status::idForEntityCode('VENDOR_APPROVAL', $code);
    }
}
