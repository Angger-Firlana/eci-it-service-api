<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Models\VendorApproval;

class GetVendorApprovalIdsByServiceRequestId
{
    public function execute(int $serviceRequestId): array
    {
        return VendorApproval::where('service_request_id', $serviceRequestId)->pluck('id')->toArray();
    }
}

