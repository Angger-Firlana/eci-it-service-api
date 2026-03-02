<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceType;

class UpdateDeviceType
{
    public function execute(int $id, array $data): DeviceType
    {
        $deviceType = DeviceType::findOrFail($id);

        $deviceType->update([
            'name' => $data['name'],
        ]);

        return $deviceType;
    }
}
