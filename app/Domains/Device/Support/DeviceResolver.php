<?php

namespace App\Domains\Device\Support;

use App\Models\Device;
use App\Models\DeviceModel;

class DeviceResolver
{
    public function findOrCreateDeviceModel(array $data): DeviceModel
    {
        return DeviceModel::firstOrCreate(
            [
                'device_type_id' => $data['device_type_id'],
                'brand' => $data['brand'],
                'model' => $data['model'],
            ],
            [
                'device_type_id' => $data['device_type_id'],
                'brand' => $data['brand'],
                'model' => $data['model'],
            ]
        );
    }

    public function findOrCreateDeviceBySerial(int $deviceModelId, string $serialNumber): Device
    {
        return Device::firstOrCreate(
            [
                'serial_number' => $serialNumber,
            ],
            [
                'device_model_id' => $deviceModelId,
                'serial_number' => $serialNumber,
            ]
        );
    }

    public function findOrCreateDeviceFromRequest(array $data): Device
    {
        $deviceModel = $this->findOrCreateDeviceModel([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
        ]);

        $device = $this->findOrCreateDeviceBySerial(
            $deviceModel->id,
            $data['serial_number']
        );

        $device->load('device_model');

        return $device;
    }
}
