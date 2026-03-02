<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceType;

class CreateDeviceType
{
    public function execute(array $data): DeviceType
    {
        return DeviceType::create([
            'name' => $data['name'],
        ]);
    }
}
