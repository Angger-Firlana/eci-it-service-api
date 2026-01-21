<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceCost;
use App\Models\ServiceRequest;

class ServiceRequestCostService
{
    public function addCost(int $serviceRequestId, array $data): ServiceCost
    {
        $cost = ServiceCost::create([
            'service_request_id' => $serviceRequestId,
            'cost_type_id' => $data['cost_type_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return $cost->load('cost_type');
    }

    public function removeCost(int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        $cost->delete();
    }

    public function getCostsByRequest(int $serviceRequestId)
    {
        return ServiceCost::where('service_request_id', $serviceRequestId)
            ->with('cost_type')
            ->get();
    }
}
