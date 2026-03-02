<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;

class UpdateDeviceModel
{
    public function execute(int $id, array $data): DeviceModel
    {
        $deviceModel = DeviceModel::findOrFail($id);

        $deviceModel->update([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
        ]);

        return $deviceModel;
    }
}
