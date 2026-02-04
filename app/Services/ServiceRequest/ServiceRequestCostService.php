<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceCost;
use App\Models\ServiceRequest;

class ServiceRequestCostService
{
    public function addCost(int $serviceRequestId, array $data): ServiceCost
    {

        $data = $data;
        if(isset($data['image'])){
            $data['image_path'] =  $data['image']->store('service_costs', 'public');
        }

        $serviceCost = ServiceCost::create($data);

        return $serviceCost->load('cost_type');
    }

    public function updateCost($serviceRequestId,int $costId, array $data): ServiceCost
    {
        $cost = ServiceCost::findOrFail($costId);
        if($serviceRequestId != $cost->service_request_id){
            throw new \Exception('Service request id not match');
        }

        if(isset($data['image'])){
            $data['image_path'] =  $data['image']->store('service_costs', 'public');
        }

        $cost->update([
            'cost_type_id' => $data['cost_type_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
        ]);

        return $cost->load('cost_type');
    }

    public function removeCost($serviceRequestId, int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        if($serviceRequestId !== $cost->service_request_id){
            throw new \Exception('Service request id not match');
        }
        $cost->delete();
    }

    public function getCostsByRequest(int $serviceRequestId)
    {
        return ServiceCost::where('service_request_id', $serviceRequestId)
            ->with('cost_type')
            ->get();
    }
}
