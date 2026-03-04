<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Device\Actions\ListDeviceTypes;
use App\Domains\Device\Actions\GetDeviceTypeById;
use App\Domains\Device\Actions\CreateDeviceType;
use App\Domains\Device\Actions\UpdateDeviceType;
use App\Domains\Device\Actions\DeleteDeviceType;
use App\Http\Requests\DeviceType\PutDeviceTypeRequest;
use App\Http\Requests\DeviceType\PostDeviceTypeRequest;
use App\Helpers\APIResponse;

class DeviceTypeController extends Controller
{
    protected ListDeviceTypes $listDeviceTypes;
    protected GetDeviceTypeById $getDeviceTypeById;
    protected CreateDeviceType $createDeviceType;
    protected UpdateDeviceType $updateDeviceType;
    protected DeleteDeviceType $deleteDeviceType;

    public function __construct(
        ListDeviceTypes $listDeviceTypes,
        GetDeviceTypeById $getDeviceTypeById,
        CreateDeviceType $createDeviceType,
        UpdateDeviceType $updateDeviceType,
        DeleteDeviceType $deleteDeviceType
    ) {
        $this->listDeviceTypes = $listDeviceTypes;
        $this->getDeviceTypeById = $getDeviceTypeById;
        $this->createDeviceType = $createDeviceType;
        $this->updateDeviceType = $updateDeviceType;
        $this->deleteDeviceType = $deleteDeviceType;
    }

    public function index(Request $request)
    {
        $paginator = $this->listDeviceTypes->execute($request->search);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);

        return APIResponse::success($data, 200, 'Device Type Found', $meta);
    }

    public function show(int $id)
    {
        $data = $this->getDeviceTypeById->execute($id);

        return APIResponse::success($data, 200, 'Device Type Found');
    }

    public function store(PostDeviceTypeRequest $request)
    {
        $validated = $request->validated();

        $data = $this->createDeviceType->execute($validated);

        return APIResponse::success($data, 201, 'Device Type Created');
    }

    public function update(PutDeviceTypeRequest $request, int $id)
    {
        $validated = $request->validated();

        $data = $this->updateDeviceType->execute($id, $validated);

        return APIResponse::success($data, 200, 'Device Type Updated');
    }

    public function destroy(int $id)
    {
        $this->deleteDeviceType->execute($id);

        return APIResponse::success(null, 200, 'Device Type Deleted');
    }
}
