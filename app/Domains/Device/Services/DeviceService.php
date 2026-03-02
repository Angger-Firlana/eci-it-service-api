<?php

namespace App\Domains\Device\Services;

use App\Domains\Device\Actions\ListDevices;
use App\Domains\Device\Actions\GetDeviceById;
use App\Domains\Device\Actions\CreateDevice;
use App\Domains\Device\Actions\UpdateDevice;
use App\Domains\Device\Actions\PatchDevice;
use App\Domains\Device\Actions\DeleteDevice;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Device;

class DeviceService
{
    public function __construct(
        protected ListDevices $listDevices,
        protected GetDeviceById $getDeviceById,
        protected CreateDevice $createDevice,
        protected UpdateDevice $updateDevice,
        protected PatchDevice $patchDevice,
        protected DeleteDevice $deleteDevice
    ) {
    }

    public function getAllDevice(Request $request): LengthAwarePaginator
    {
        return $this->listDevices->execute($request);
    }

    public function getDeviceById(int $id): Device
    {
        return $this->getDeviceById->execute($id);
    }

    public function createDevice(array $data): Device
    {
        return $this->createDevice->execute($data);
    }

    public function updateDevice(int $id, array $data): Device
    {
        return $this->updateDevice->execute($id, $data);
    }

    public function patchDevice(int $id, array $data): Device
    {
        return $this->patchDevice->execute($id, $data);
    }

    public function deleteDevice(int $id): void
    {
        $this->deleteDevice->execute($id);
    }
}
