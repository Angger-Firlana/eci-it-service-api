<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;

class DeleteDeviceModel
{
    public function execute(int $id): void
    {
        $deviceModel = DeviceModel::findOrFail($id);
        $deviceModel->delete();
    }
}
