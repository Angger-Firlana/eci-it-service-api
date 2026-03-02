<?php

namespace App\Domains\Vendor\Actions;

use App\Models\Vendor;

class UpdateVendor
{
    public function execute(int $id, array $data): Vendor
    {
        $vendor = Vendor::findOrFail($id);

        if (isset($data['name'])) {
            $vendor->name = $data['name'];
        }

        if (isset($data['maps_url'])) {
            $vendor->maps_url = $data['maps_url'];
        }

        if (isset($data['description'])) {
            $vendor->description = $data['description'];
        }

        $vendor->save();

        return $vendor;
    }
}
