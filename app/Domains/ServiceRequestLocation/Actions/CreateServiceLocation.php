<?php

namespace App\Domains\ServiceRequestLocation\Actions;

use App\Models\ServiceLocation;

class CreateServiceLocation
{
    public function execute(int $serviceRequestId, array $data): ServiceLocation
    {
        $serviceLocation = ServiceLocation::create([
            'service_request_id' => $serviceRequestId,
            'location_type' => $data['location_type'],
            'vendor_id' => $data['vendor_id'] ?? null,
            'address' => $data['address'] ?? null,
            'maps_url' => $data['maps_url'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        return $serviceLocation->load('vendor');
    }
}

