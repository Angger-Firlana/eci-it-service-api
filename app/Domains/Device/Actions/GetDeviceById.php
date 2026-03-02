<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;

class GetDeviceById
{
    public function execute(int $id): Device
    {
        return Device::findOrFail($id);
    }
}
