<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;

class PatchDeviceModel
{
    public function execute(int $id, array $data): DeviceModel
    {
        $deviceModel = DeviceModel::findOrFail($id);

        if (isset($data['device_type_id'])) {
            $deviceModel->device_type_id = $data['device_type_id'];
        }

        if (isset($data['brand'])) {
            $deviceModel->brand = $data['brand'];
        }

        if (isset($data['model'])) {
            $deviceModel->model = $data['model'];
        }

        $deviceModel->save();

        return $deviceModel;
    }
}
