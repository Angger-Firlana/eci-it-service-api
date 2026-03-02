<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;

class CreateDeviceModel
{
    public function execute(array $data): DeviceModel
    {
        return DeviceModel::create([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
        ]);
    }
}
