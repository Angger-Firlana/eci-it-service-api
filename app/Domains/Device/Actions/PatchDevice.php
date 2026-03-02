<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;

class PatchDevice
{
    public function execute(int $id, array $data): Device
    {
        $device = Device::findOrFail($id);

        if (isset($data['device_model_id'])) {
            $device->device_model_id = $data['device_model_id'];
        }

        if (isset($data['serial_number'])) {
            $device->serial_number = $data['serial_number'];
        }

        if (isset($data['bad_asset'])) {
            $device->bad_asset = $data['bad_asset'];
        }

        $device->save();

        return $device;
    }
}
