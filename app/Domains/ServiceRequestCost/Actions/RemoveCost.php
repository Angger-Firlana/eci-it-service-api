<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Models\ServiceCost;

class RemoveCost
{
    public function execute(int $serviceRequestId, int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId !== $cost->service_request_id) {
            throw new \Exception('Service request id not match');
        }

        $cost->delete();
    }
}

