<?php

namespace App\Domains\Device\Services;

use App\Domains\Device\Actions\ListDeviceModels;
use App\Domains\Device\Actions\GetDeviceModelById;
use App\Domains\Device\Actions\CreateDeviceModel;
use App\Domains\Device\Actions\UpdateDeviceModel;
use App\Domains\Device\Actions\PatchDeviceModel;
use App\Domains\Device\Actions\DeleteDeviceModel;
use App\Models\DeviceModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeviceModelService
{
    public function __construct(
        protected ListDeviceModels $listDeviceModels,
        protected GetDeviceModelById $getDeviceModelById,
        protected CreateDeviceModel $createDeviceModel,
        protected UpdateDeviceModel $updateDeviceModel,
        protected PatchDeviceModel $patchDeviceModel,
        protected DeleteDeviceModel $deleteDeviceModel
    ) {
    }

    public function getAllDeviceModel(?string $keyword): LengthAwarePaginator
    {
        return $this->listDeviceModels->execute($keyword);
    }

    public function getDeviceModelById(int $id): DeviceModel
    {
        return $this->getDeviceModelById->execute($id);
    }

    public function createDeviceModel(array $data): DeviceModel
    {
        return $this->createDeviceModel->execute($data);
    }

    public function updateDeviceModel(int $id, array $data): DeviceModel
    {
        return $this->updateDeviceModel->execute($id, $data);
    }

    public function patchDeviceModel(int $id, array $data): DeviceModel
    {
        return $this->patchDeviceModel->execute($id, $data);
    }

    public function deleteDeviceModel(int $id): void
    {
        $this->deleteDeviceModel->execute($id);
    }
}
