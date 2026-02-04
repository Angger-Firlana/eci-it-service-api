<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceLocation;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Collection;

class ServiceLocationService
{
    public function createServiceLocation(int $serviceRequestId, array $data): ServiceLocation
    {
        $serviceLocation = ServiceLocation::create([
            'service_request_id' => $serviceRequestId,
            'location_type' => $data['location_type'],
            'vendor_id' => $data['vendor_id'] ?? null,
            'address' => $data['address'] ?? null,
            'maps_url' => $data['maps_url'] ?? null,
            'phone_number' => $data['phone_number'],
            'is_active' => $data['is_active']
        ]);
        

        return $serviceLocation->load('vendor');
    }

    public function updateServiceLocation(int $serviceRequestId, int $locationId, array $data): ServiceLocation
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

        if(array_key_exists('phone_number')){
            $updateData['phone_number'] = $data['phone_number'];
        };

        if (array_key_exists('maps_url', $data)) {
            $updateData['maps_url'] = $data['maps_url'];
        }

        if (!empty($updateData)) {
            $serviceLocation->update($updateData);
        }

        return $serviceLocation->load('vendor');
    }

    public function deleteServiceLocation(int $id): void
    {
        $serviceLocation = ServiceLocation::findOrFail($id);
        $serviceLocation->delete();
    }

    public function getLocationsByServiceRequestId($serviceRequestId): Collection{
        $locations = ServiceLocation::with('vendor')
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $locations;
    }

    public function getLocationById(int $id): ServiceLocation
    {
        $location = ServiceLocation::with(['service_request', 'vendor'])->findOrFail($id);

        return $location;
    }
}
