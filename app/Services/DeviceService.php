<?php

namespace App\Services;

use App\Models\Device;
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
                $query->where('name', 'like', '%' . $request->input('model') . '%');
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
}
