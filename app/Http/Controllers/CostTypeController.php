<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CostType\StoreCostTypeRequest;
use App\Http\Requests\CostType\UpdateCostTypeRequest;
use App\Domains\MasterData\Actions\CreateCostType;
use App\Domains\MasterData\Actions\DeleteCostType;
use App\Domains\MasterData\Actions\GetCostTypeById;
use App\Domains\MasterData\Actions\ListCostTypes;
use App\Domains\MasterData\Actions\UpdateCostType;
use App\Helpers\APIResponse;

class CostTypeController extends Controller
{
    protected ListCostTypes $listCostTypes;
    protected GetCostTypeById $getCostTypeById;
    protected CreateCostType $createCostType;
    protected UpdateCostType $updateCostType;
    protected DeleteCostType $deleteCostType;
    
    public function __construct(
        ListCostTypes $listCostTypes,
        GetCostTypeById $getCostTypeById,
        CreateCostType $createCostType,
        UpdateCostType $updateCostType,
        DeleteCostType $deleteCostType
    ) {
        $this->listCostTypes = $listCostTypes;
        $this->getCostTypeById = $getCostTypeById;
        $this->createCostType = $createCostType;
        $this->updateCostType = $updateCostType;
        $this->deleteCostType = $deleteCostType;
    }

    public function index(Request $request)
    {
        $costTypes = $this->listCostTypes->execute($request);
        return APIResponse::success($costTypes, 200, 'Cost types retrieved successfully');
    }

    public function store(StoreCostTypeRequest $request)
    {
        $costType = $this->createCostType->execute($request->validated());
        return APIResponse::success($costType, 201, 'Cost type created successfully');
    }

    public function show($id)
    {
        $costType = $this->getCostTypeById->execute((int) $id);
        return APIResponse::success($costType, 200, 'Cost type retrieved successfully');
    }

    public function update(UpdateCostTypeRequest $request, $id)
    {
        $costType = $this->updateCostType->execute((int) $id, $request->validated());
        return APIResponse::success($costType, 200, 'Cost type updated successfully');
    }

    public function destroy($id)
    {
        $this->deleteCostType->execute((int) $id);
        return APIResponse::success(null, 204, 'Cost type deleted successfully');
    }
}
