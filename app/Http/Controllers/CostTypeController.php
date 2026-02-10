<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CostType\StoreCostTypeRequest;
use App\Http\Requests\CostType\UpdateCostTypeRequest;
use App\Services\MasterData\CostTypeService;
use App\Helpers\APIResponse;

class CostTypeController extends Controller
{
    protected $costTypeService;
    
    public function __construct(
        CostTypeService $costTypeService
    ) {
        $this->costTypeService = $costTypeService;
    }

    public function index(Request $request)
    {
        $costTypes = $this->costTypeService->getAllCostTypes($request);
        return APIResponse::success($costTypes, 200, 'Cost types retrieved successfully');
    }

    public function store(StoreCostTypeRequest $request)
    {
        $costType = $this->costTypeService->createCostType($request->validated());
        return APIResponse::success($costType, 201, 'Cost type created successfully');
    }

    public function show($id)
    {
        $costType = $this->costTypeService->getCostTypeById($id);
        return APIResponse::success($costType, 200, 'Cost type retrieved successfully');
    }

    public function update(UpdateCostTypeRequest $request, $id)
    {
        $costType = $this->costTypeService->updateCostType($id, $request->validated());
        return APIResponse::success($costType, 200, 'Cost type updated successfully');
    }

    public function destroy($id)
    {
        $this->costTypeService->deleteCostType($id);
        return APIResponse::success(null, 204, 'Cost type deleted successfully');
    }
}
