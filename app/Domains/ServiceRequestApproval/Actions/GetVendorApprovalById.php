<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Models\VendorApproval;

class GetVendorApprovalById
{
    public function execute(int $id): VendorApproval
    {
        return VendorApproval::with(['approver', 'assigned_by', 'service_request'])
            ->findOrFail($id);
    }
}

