<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\DeviceService;
use App\Models\Device;
use App\Http\Requests\Device\StoreDeviceRequest;
use App\Http\Requests\Device\UpdateDeviceRequest;

class DeviceController extends Controller
{
    protected $deviceService;
    //
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    
    public function index(Request $request)
    {
        //
        $data = $this->deviceService->getAllDevice($request);

        return APIResponse::success($data, 200, "");
    }
    
    public function show($id)
    {
        $data = $this->deviceService->getDeviceById($id);

        return APIResponse::success($data, 200, "");
    }
    
    public function store(StoreDeviceRequest $request)
    {
        //
        $data = $this->deviceService->createDevice($request->validated());

        return APIResponse::success($data, 201, "");
    }
    
    public function update($id, UpdateDeviceRequest $request)
    {
        //
        $data = $this->deviceService->updateDevice($id, $request->validated());

        return APIResponse::success($data, 200, "");
    }

    public function patch($id, UpdateDeviceRequest $request)
    {
        //
        $data = $this->deviceService->patchDevice($id, $request->validated());

        return APIResponse::success($data, 200, "");
    }
    
    public function destroy($id)
    {
        //
        $data = $this->deviceService->deleteDevice($id);

        return APIResponse::success($data, 200, "");
    }
}
