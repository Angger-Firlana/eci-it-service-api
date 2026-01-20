<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceLocation;
use App\Models\ServiceRequest;

class ServiceLocationService
{
    public function createServiceLocation(array $data, ServiceRequest $serviceRequest): ServiceLocation
    {
        $serviceLocation = ServiceLocation::create([
            'service_request_id' => $serviceRequest->id,
            'location_type' => $data['location_type'] ?? 'internal',
            'vendor_id' => $data['vendor_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $serviceLocation->load('vendor');
    }

    public function updateServiceLocation(ServiceLocation $serviceLocation, array $data): ServiceLocation
    {
        $updateData = [
            'location_type' => $data['location_type'] ?? $serviceLocation->location_type,
            'vendor_id' => $data['vendor_id'] ?? $serviceLocation->vendor_id,
            'is_active' => $data['is_active'] ?? $serviceLocation->is_active,
        ];
        
        $serviceLocation->update(array_filter($updateData));

        return $serviceLocation->load('vendor');
    }

    public function deleteServiceLocation(int $id): void
    {
        $serviceLocation = ServiceLocation::findOrFail($id);
        $serviceLocation->delete();
    }

    public function getLocationById(int $id): ServiceLocation
    {
        $location = ServiceLocation::with(['serviceRequest', 'vendor'])->findOrFail($id);

        return $location;
    }

    public function getLocationsByServiceRequest(int $serviceRequestId): Collection
    {
        $locations = ServiceLocation::with('vendor')
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $locations;
    }
}
