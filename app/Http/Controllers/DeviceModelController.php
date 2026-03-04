<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Device\Actions\ListDeviceModels;
use App\Domains\Device\Actions\GetDeviceModelById;
use App\Domains\Device\Actions\CreateDeviceModel;
use App\Domains\Device\Actions\UpdateDeviceModel;
use App\Domains\Device\Actions\PatchDeviceModel;
use App\Domains\Device\Actions\DeleteDeviceModel;
use App\Http\Requests\DeviceModel\PostDeviceModelRequest;
use App\Http\Requests\DeviceModel\PutDeviceModelRequest;

class DeviceModelController extends Controller
{
    protected ListDeviceModels $listDeviceModels;
    protected GetDeviceModelById $getDeviceModelById;
    protected CreateDeviceModel $createDeviceModel;
    protected UpdateDeviceModel $updateDeviceModel;
    protected PatchDeviceModel $patchDeviceModel;
    protected DeleteDeviceModel $deleteDeviceModel;

    
    public function __construct(
        ListDeviceModels $listDeviceModels,
        GetDeviceModelById $getDeviceModelById,
        CreateDeviceModel $createDeviceModel,
        UpdateDeviceModel $updateDeviceModel,
        PatchDeviceModel $patchDeviceModel,
        DeleteDeviceModel $deleteDeviceModel
    ){
        $this->listDeviceModels = $listDeviceModels;
        $this->getDeviceModelById = $getDeviceModelById;
        $this->createDeviceModel = $createDeviceModel;
        $this->updateDeviceModel = $updateDeviceModel;
        $this->patchDeviceModel = $patchDeviceModel;
        $this->deleteDeviceModel = $deleteDeviceModel;
    }

    public function index(Request $request){
        $paginator = $this->listDeviceModels->execute($request->keyword);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        
        return APIResponse::success($data, 200, "Device Model Found", $meta);
    }

    public function show(int $id){
        $data = $this->getDeviceModelById->execute($id);
        return APIResponse::success($data, 200, "Device Model Found");
    }

    public function store(PostDeviceModelRequest $request){
        $data = $this->createDeviceModel->execute($request->validated());
        return APIResponse::success($data, 201, "Device Model Create Successfully");
    }

    public function update($id, PutDeviceModelRequest $request){
        $data = $this->updateDeviceModel->execute((int) $id, $request->validated());
        return APIResponse::success($data, 200, "Device Model Update Successfully");
    }

    public function patch($id, PutDeviceModelRequest $request){
        $data = $this->patchDeviceModel->execute((int) $id, $request->validated());
        return APIResponse::success($data, 200, "Device Model Patch Successfully");
    }

    public function destroy($id){
        $this->deleteDeviceModel->execute((int) $id);
        return APIResponse::success(null, 200, "Device Model Delete Successfully");
    }
}
