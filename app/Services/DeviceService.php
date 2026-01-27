<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceModel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class DeviceService
{
    //
    public function getAllDevice(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $devices = Device::select(['id','device_model_id','serial_number'])->with(['device_model:id,brand,model']);

        if($request->has('serial-number')){
            $devices = $devices->where('serial_number', $request->input('serial-number'));
        }

        if($request->has('brand')){
            $devices = $devices->whereHas('device_model', function($query) use ($request) {
                $query->where('brand', $request->input('brand'));
            });
        }

        if($request->has('model')){
            $devices = $devices->whereHas('device_model', function($query) use ($request) {
                $query->where('model', 'like', '%' . $request->input('model') . '%');
            });
        }

        return $devices->paginate($request->get('per_page', 15));
    }

    public function getDeviceById(int $id):Device{
        $device = Device::findOrFail($id);

        return $device;
    }

    public function createDevice(array $data): Device{
        $device = Device::create([
            'device_model_id' => $data['device_model_id'],
            'serial_number' => $data['serial_number']
        ]);

        return $device;
    }

    public function updateDevice(int $id, array $data):Device{
        $device = Device::findOrFail($id);

        $device->update([
            'device_model_id' => $data['device_model_id'],
            'serial_number' => $data['serial_number']
        ]);

        return $device;
    }

    public function patchDevice(int $id, array $data):Device{
        $device = Device::findOrFail($id);

        if(isset($data['device_model_id'])){
            $device->device_model_id = $data['device_model_id'];
        }

        if(isset($data['serial_number'])){
            $device->serial_number = $data['serial_number'];
        }

        $device->save();

        return $device;
    }

    public function deleteDevice(int $id):void{
        $device = Device::findOrFail($id);
        $device->delete();
    }

    /**
     * Find or create a device model based on device_type_id, brand, and model
     *
     * @param array $data
     * @return DeviceModel
     */
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

    /**
     * Find or create a device based on device_model_id and serial_number
     *
     * @param int $deviceModelId
     * @param string $serialNumber
     * @return Device
     */
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

    /**
     * Find or create device from request data
     * This is the main method that combines both operations
     *
     * @param array $data
     * @return Device
     */
    public function findOrCreateDeviceFromRequest(array $data): Device
    {
        // First, find or create the device model
        $deviceModel = $this->findOrCreateDeviceModel([
            'device_type_id' => $data['device_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
        ]);

        // Then, find or create the device with the serial number
        $device = $this->findOrCreateDeviceBySerial(
            $deviceModel->id,
            $data['serial_number']
        );

        // Load the device_model relationship for the response
        $device->load('device_model');

        return $device;
    }
}
