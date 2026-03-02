<?php

namespace App\Domains\ServiceRequestLocation\Actions;

use App\Models\ServiceLocation;

class UpdateServiceLocation
{
    public function execute(int $serviceRequestId, int $locationId, array $data): ServiceLocation
    {
        $serviceLocation = ServiceLocation::findOrFail($locationId);

        $updateData = [];
        if (array_key_exists('location_type', $data)) {
            $updateData['location_type'] = $data['location_type'];
        }

        if (array_key_exists('vendor_id', $data)) {
            $updateData['vendor_id'] = $data['vendor_id'];
        }

        if (array_key_exists('is_active', $data)) {
            $updateData['is_active'] = $data['is_active'];
        }

        if (array_key_exists('address', $data)) {
            $updateData['address'] = $data['address'];
        }

        if (array_key_exists('phone_number', $data)) {
            $updateData['phone_number'] = $data['phone_number'];
        }

        if (array_key_exists('maps_url', $data)) {
            $updateData['maps_url'] = $data['maps_url'];
        }

        if (!empty($updateData)) {
            $serviceLocation->update($updateData);
        }

        return $serviceLocation->load('vendor');
    }
}

