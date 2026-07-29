<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Models\VendorApproval;
use Illuminate\Database\Eloquent\Collection;

class ListVendorApprovalsByServiceRequestId
{
    public function execute(int $serviceRequestId): Collection
    {
        return VendorApproval::with(['approver', 'assigned_by', 'status'])
            ->where('service_request_id', $serviceRequestId)
            ->get();
    }
}

