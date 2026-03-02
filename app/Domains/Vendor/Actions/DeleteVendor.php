<?php

namespace App\Domains\Vendor\Actions;

use App\Models\Vendor;

class DeleteVendor
{
    public function execute(int $id): void
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
    }
}
