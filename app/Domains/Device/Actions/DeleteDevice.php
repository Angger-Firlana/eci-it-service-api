<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;

class DeleteDevice
{
    public function execute(int $id): void
    {
        $device = Device::findOrFail($id);
        $device->delete();
    }
}
