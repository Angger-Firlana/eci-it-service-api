<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;

class UpdateDevice
{
    public function execute(int $id, array $data): Device
    {
        $device = Device::findOrFail($id);

        $device->update([
            'device_model_id' => $data['device_model_id'],
            'serial_number' => $data['serial_number'],
            'bad_asset' => $data['bad_asset'] ?? $device->bad_asset,
        ]);

        return $device;
    }
}
