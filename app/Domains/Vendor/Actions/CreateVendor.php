<?php

namespace App\Domains\Vendor\Actions;

use App\Models\Vendor;

class CreateVendor
{
    public function execute(array $data): Vendor
    {
        return Vendor::create(array_filter($data));
    }
}
