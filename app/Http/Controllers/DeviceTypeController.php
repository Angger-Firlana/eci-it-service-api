<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeviceTypeService;

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

        return response()->json([
            'code' => 200,
            'data' => $data
        ]);
    }

    public function show(int $id)
    {
        $data = $this->deviceTypeService->getById($id);

        return response()->json([
            'code' => 200,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $data = $this->deviceTypeService->create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'Device Type Created',
            'data' => $data
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $data = $this->deviceTypeService->update($id, $validated);

        return response()->json([
            'code' => 200,
            'message' => 'Device Type Updated',
            'data' => $data
        ]);
    }

    public function destroy(int $id)
    {
        $this->deviceTypeService->delete($id);

        return response()->json([
            'code' => 200,
            'message' => 'Device Type Deleted'
        ]);
    }
}
