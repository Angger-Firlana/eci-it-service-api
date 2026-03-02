<?php

namespace App\Domains\DetailServiceRequest\Actions;

use App\Models\Device;
use App\Domains\Device\Support\DeviceResolver;

class CheckOrCreateDevice{
    protected DeviceResolver $deviceResolver;

    public function __construct(DeviceResolver $deviceResolver)
    {
        $this->deviceResolver = $deviceResolver;
    }

    public function execute(array $deviceData):Device{
        $device = $this->deviceResolver->findOrCreateDeviceFromRequest([
                'device_type_id' => $deviceData['device_type_id'],
                'brand'          => $deviceData['brand'],
                'model'          => $deviceData['model'],
                'serial_number'  => $deviceData['serial_number'],
        ]);
        return $device;
    }
}