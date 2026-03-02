<?php
namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use App\Models\Device;

class MarkDeviceAsBadAsset{
    public function execute(ServiceRequest $serviceRequest): void
    {
        $deviceIds = $serviceRequest->service_request_details()
            ->whereNotNull('device_id')
            ->pluck('device_id')
            ->unique()
            ->values();

        if ($deviceIds->isEmpty()) {
            return;
        }

        Device::whereIn('id', $deviceIds)->update(['bad_asset' => true]);
    }
}