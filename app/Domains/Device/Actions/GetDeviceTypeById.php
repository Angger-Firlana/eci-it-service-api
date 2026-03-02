<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceType;

class GetDeviceTypeById
{
    public function execute(int $id): DeviceType
    {
        return DeviceType::findOrFail($id);
    }
}
