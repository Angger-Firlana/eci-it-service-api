<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Device\Services\DeviceModelService;
use App\Http\Requests\DeviceModel\PostDeviceModelRequest;
use App\Http\Requests\DeviceModel\PutDeviceModelRequest;

class DeviceModelController extends Controller
{
    protected $deviceModelService;

    
    public function __construct(
        DeviceModelService $deviceModelService
    ){
        $this->deviceModelService = $deviceModelService;
    }

    public function index(Request $request){
        $paginator = $this->deviceModelService->getAllDeviceModel($request->keyword);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        
        return APIResponse::success($data, 200, "Device Model Found", $meta);
    }

    public function show(int $id){
        $data = $this->deviceModelService->getDeviceModelById($id);
        return APIResponse::success($data, 200, "Device Model Found");
    }

    public function store(PostDeviceModelRequest $request){
        $data = $this->deviceModelService->createDeviceModel($request->validated());
        return APIResponse::success($data, 201, "Device Model Create Successfully");
    }

    public function update($id, PutDeviceModelRequest $request){
        $data = $this->deviceModelService->updateDeviceModel($id, $request->validated());
        return APIResponse::success($data, 200, "Device Model Update Successfully");
    }

    public function patch($id, PutDeviceModelRequest $request){
        $data = $this->deviceModelService->patchDeviceModel($id, $request->validated());
        return APIResponse::success($data, 200, "Device Model Patch Successfully");
    }

    public function destroy($id){
        $this->deviceModelService->deleteDeviceModel($id);
        return APIResponse::success(null, 200, "Device Model Delete Successfully");
    }
}
