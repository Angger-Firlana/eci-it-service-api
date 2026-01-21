<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ServiceRequest\ServiceRequestCostService;

class ServiceRequestCostController extends Controller
{
    protected $costService;

    public function __construct(ServiceRequestCostService $costService)
    {
        $this->costService = $costService;
    }

    public function index($id)
    {
        $costs = $this->costService->getCostsByRequest($id);
        return APIResponse::success($costs);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'cost_type_id' => 'required|exists:cost_types,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $cost = $this->costService->addCost($id, $request->all());
        return APIResponse::success($cost, 201, 'Cost added successfully');
    }

    public function destroy($id, $costId)
    {
        $this->costService->removeCost($costId);
        return APIResponse::success(null, 200, 'Cost removed successfully');
    }
}
