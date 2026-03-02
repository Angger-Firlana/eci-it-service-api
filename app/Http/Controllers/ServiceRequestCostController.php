<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestCost\Services\ServiceRequestCostService;
use App\Http\Requests\ServiceCost\StoreServiceCostRequest;
use App\Http\Requests\ServiceCost\UpdateServiceCostRequest;

class ServiceRequestCostController extends Controller
{
    protected $costService;

    public function __construct(ServiceRequestCostService $costService)
    {
        $this->costService = $costService;
    }

    public function index($serviceRequestId)
    {
        $costs = $this->costService->getCostsByRequest($serviceRequestId);
        return APIResponse::success($costs);
    }
    public function store(StoreServiceCostRequest $request, $serviceRequestId)
    {

        $cost = $this->costService->addCost($serviceRequestId, $request->validated());
        return APIResponse::success($cost, 201, 'Cost added successfully');
    }

    public function update(UpdateServiceCostRequest $request, $serviceRequestId, $id){
        $cost = $this->costService->updateCost($serviceRequestId, $id, $request->validated());
        return APIResponse::success($cost, 200, 'Cost updated successfully');
    }

    public function destroy($serviceRequestId, $costId)
    {
        $this->costService->removeCost($serviceRequestId, $costId);
        return APIResponse::success(null, 200, 'Cost removed successfully');
    }

    public function attachment($serviceRequestId, $costId)
    {
        return $this->costService->getAttachment($serviceRequestId, $costId);
    }
}
