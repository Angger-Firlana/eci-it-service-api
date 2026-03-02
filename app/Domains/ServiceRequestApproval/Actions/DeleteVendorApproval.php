<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Models\VendorApproval;

class DeleteVendorApproval
{
    public function execute(int $id): void
    {
        $approval = VendorApproval::findOrFail($id);
        $approval->delete();
    }
}

