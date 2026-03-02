<?php

namespace App\Domains\Device\Services;

use App\Domains\Device\Actions\ListDeviceTypes;
use App\Domains\Device\Actions\GetDeviceTypeById;
use App\Domains\Device\Actions\CreateDeviceType;
use App\Domains\Device\Actions\UpdateDeviceType;
use App\Domains\Device\Actions\DeleteDeviceType;
use App\Models\DeviceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeviceTypeService
{
    public function __construct(
        protected ListDeviceTypes $listDeviceTypes,
        protected GetDeviceTypeById $getDeviceTypeById,
        protected CreateDeviceType $createDeviceType,
        protected UpdateDeviceType $updateDeviceType,
        protected DeleteDeviceType $deleteDeviceType
    ) {
    }

    public function getAll(?string $search = null): LengthAwarePaginator
    {
        return $this->listDeviceTypes->execute($search);
    }

    public function getById(int $id): DeviceType
    {
        return $this->getDeviceTypeById->execute($id);
    }

    public function create(array $data): DeviceType
    {
        return $this->createDeviceType->execute($data);
    }

    public function update(int $id, array $data): DeviceType
    {
        return $this->updateDeviceType->execute($id, $data);
    }

    public function delete(int $id): void
    {
        $this->deleteDeviceType->execute($id);
    }
}
