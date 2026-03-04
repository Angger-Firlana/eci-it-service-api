<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Device\Actions\ListDevices;
use App\Domains\Device\Actions\GetDeviceById;
use App\Domains\Device\Actions\CreateDevice;
use App\Domains\Device\Actions\UpdateDevice;
use App\Domains\Device\Actions\PatchDevice;
use App\Domains\Device\Actions\DeleteDevice;
use App\Http\Requests\Device\StoreDeviceRequest;
use App\Http\Requests\Device\UpdateDeviceRequest;

class DeviceController extends Controller
{
    protected ListDevices $listDevices;
    protected GetDeviceById $getDeviceById;
    protected CreateDevice $createDevice;
    protected UpdateDevice $updateDevice;
    protected PatchDevice $patchDevice;
    protected DeleteDevice $deleteDevice;

    public function __construct(
        ListDevices $listDevices,
        GetDeviceById $getDeviceById,
        CreateDevice $createDevice,
        UpdateDevice $updateDevice,
        PatchDevice $patchDevice,
        DeleteDevice $deleteDevice
    )
    {
        $this->listDevices = $listDevices;
        $this->getDeviceById = $getDeviceById;
        $this->createDevice = $createDevice;
        $this->updateDevice = $updateDevice;
        $this->patchDevice = $patchDevice;
        $this->deleteDevice = $deleteDevice;
    }
    
    public function index(Request $request)
    {
        $paginator = $this->listDevices->execute($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);

        return APIResponse::success($data, 200, "", $meta);
    }
    
    public function show($id)
    {
        $data = $this->getDeviceById->execute((int) $id);

        return APIResponse::success($data, 200, "");
    }
    
    public function store(StoreDeviceRequest $request)
    {
        $data = $this->createDevice->execute($request->validated());

        return APIResponse::success($data, 201, "");
    }
    
    public function update($id, UpdateDeviceRequest $request)
    {
        $data = $this->updateDevice->execute((int) $id, $request->validated());

        return APIResponse::success($data, 200, "");
    }

    public function patch($id, UpdateDeviceRequest $request)
    {
        $data = $this->patchDevice->execute((int) $id, $request->validated());

        return APIResponse::success($data, 200, "");
    }
    
    public function destroy($id)
    {
        $data = $this->deleteDevice->execute((int) $id);

        return APIResponse::success($data, 200, "");
    }
}
