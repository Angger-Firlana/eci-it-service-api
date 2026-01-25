<?php

namespace App\Services;

use App\Models\DeviceModel;
use Illuminate\Database\Eloquent\Collection;

class DeviceModelService{
    public function getAllDeviceModel(?string $keyword): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $deviceModels = DeviceModel::query()->when($keyword, fn($q) => $q->where('model', 'LIKE', "%{$keyword}%"))->paginate(15);

        return $deviceModels;
    }

    public function getDeviceModelById(int $id):DeviceModel{
        $deviceModel = DeviceModel::findOrFail($id);
        return $deviceModel;
    }

    public function createDeviceModel(array $data): DeviceModel{
        $deviceModel = DeviceModel::create([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model']
        ]);

        return $deviceModel;
    }

    public function updateDeviceModel(int $id, array $data):DeviceModel
    {
        $deviceModel = DeviceModel::find($id);

        $deviceModel->update([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model']
        ]);

        return $deviceModel;
    }

    public function patchDeviceModel(int $id, array $data):DeviceModel{
        $deviceModel = DeviceModel::find($id);

        if(isset($data['device_type_id'])){
            $deviceModel['device_type_id'] = $data['device_type_id'];
        }

        if(isset($data['brand'])){
            $deviceModel['brand'] = $data['brand'];
        }

        if(isset($data['model'])){
            $deviceModel['model'] = $data['model'];
        }

        return $deviceModel;
    }

    public function deleteDeviceModel(int $id):void{
        $deviceModel = DeviceModel::findOrFail($id);
        $deviceModel->delete();
    }
}