<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;

class CreateDevice
{
    public function execute(array $data): Device
    {
        return Device::create([
            'device_model_id' => $data['device_model_id'],
            'serial_number' => $data['serial_number'],
            'bad_asset' => $data['bad_asset'] ?? false,
        ]);
    }
}
