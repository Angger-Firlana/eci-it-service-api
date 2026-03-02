<?php

namespace App\Domains\Vendor\Actions;

use App\Models\Vendor;

class GetVendorById
{
    public function execute(int $id): Vendor
    {
        return Vendor::findOrFail($id);
    }
}
