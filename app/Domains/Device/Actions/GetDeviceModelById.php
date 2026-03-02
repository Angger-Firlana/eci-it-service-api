<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;

class GetDeviceModelById
{
    public function execute(int $id): DeviceModel
    {
        return DeviceModel::findOrFail($id);
    }
}
