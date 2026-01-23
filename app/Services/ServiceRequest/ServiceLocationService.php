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
            'vendor_id' => $data['vendor_id'],
            'is_active' => $data['is_active'],
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
