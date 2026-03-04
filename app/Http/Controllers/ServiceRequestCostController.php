<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestCost\Actions\AddCost;
use App\Domains\ServiceRequestCost\Actions\GetAttachment;
use App\Domains\ServiceRequestCost\Actions\ListCostsByRequest;
use App\Domains\ServiceRequestCost\Actions\RemoveCost;
use App\Domains\ServiceRequestCost\Actions\UpdateCost;
use App\Http\Requests\ServiceCost\StoreServiceCostRequest;
use App\Http\Requests\ServiceCost\UpdateServiceCostRequest;

class ServiceRequestCostController extends Controller
{
    protected AddCost $addCost;
    protected UpdateCost $updateCost;
    protected RemoveCost $removeCost;
    protected ListCostsByRequest $listCostsByRequest;
    protected GetAttachment $getAttachment;

    public function __construct(
        AddCost $addCost,
        UpdateCost $updateCost,
        RemoveCost $removeCost,
        ListCostsByRequest $listCostsByRequest,
        GetAttachment $getAttachment
    )
    {
        $this->addCost = $addCost;
        $this->updateCost = $updateCost;
        $this->removeCost = $removeCost;
        $this->listCostsByRequest = $listCostsByRequest;
        $this->getAttachment = $getAttachment;
    }

    public function index($serviceRequestId)
    {
        $costs = $this->listCostsByRequest->execute((int) $serviceRequestId);
        return APIResponse::success($costs);
    }
    public function store(StoreServiceCostRequest $request, $serviceRequestId)
    {

        $cost = $this->addCost->execute((int) $serviceRequestId, $request->validated());
        return APIResponse::success($cost, 201, 'Cost added successfully');
    }

    public function update(UpdateServiceCostRequest $request, $serviceRequestId, $id){
        $cost = $this->updateCost->execute((int) $serviceRequestId, (int) $id, $request->validated());
        return APIResponse::success($cost, 200, 'Cost updated successfully');
    }

    public function destroy($serviceRequestId, $costId)
    {
        $this->removeCost->execute((int) $serviceRequestId, (int) $costId);
        return APIResponse::success(null, 200, 'Cost removed successfully');
    }

    public function attachment($serviceRequestId, $costId)
    {
        return $this->getAttachment->execute((int) $serviceRequestId, (int) $costId);
    }
}
