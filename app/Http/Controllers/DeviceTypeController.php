<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeviceTypeService;
use App\Http\Requests\DeviceType\PutDeviceTypeRequest;
use App\Http\Requests\DeviceType\PostDeviceTypeRequest;
use App\Helpers\APIResponse;

class DeviceTypeController extends Controller
{
    protected $deviceTypeService;

    public function __construct(
        DeviceTypeService $deviceTypeService
    ) {
        $this->deviceTypeService = $deviceTypeService;
    }

    public function index(Request $request)
    {
        $data = $this->deviceTypeService->getAll($request->search);

        return APIResponse::success($data, 200, 'Device Type Found');
    }

    public function show(int $id)
    {
        $data = $this->deviceTypeService->getById($id);

        return APIResponse::success($data, 200, 'Device Type Found');
    }

    public function store(PostDeviceTypeRequest $request)
    {
        $validated = $request->validated();

        $data = $this->deviceTypeService->create($validated);

        return APIResponse::success($data, 201, 'Device Type Created');
    }

    public function update(PutDeviceTypeRequest $request, int $id)
    {
        $validated = $request->validated();

        $data = $this->deviceTypeService->update($id, $validated);

        return APIResponse::success($data, 200, 'Device Type Updated');
    }

    public function destroy(int $id)
    {
        $this->deviceTypeService->delete($id);

        return APIResponse::success(null, 200, 'Device Type Deleted');
    }
}
