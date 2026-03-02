<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Models\ServiceCost;
use Illuminate\Database\Eloquent\Collection;

class ListCostsByRequest
{
    public function execute(int $serviceRequestId): Collection
    {
        return ServiceCost::where('service_request_id', $serviceRequestId)
            ->with('cost_type')
            ->get();
    }
}

