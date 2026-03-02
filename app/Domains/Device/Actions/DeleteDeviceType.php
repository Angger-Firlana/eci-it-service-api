<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceType;

class DeleteDeviceType
{
    public function execute(int $id): void
    {
        $deviceType = DeviceType::findOrFail($id);
        $deviceType->delete();
    }
}
