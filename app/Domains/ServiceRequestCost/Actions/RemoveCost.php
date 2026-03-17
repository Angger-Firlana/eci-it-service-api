<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Exceptions\ApiException;
use App\Models\ServiceCost;

class RemoveCost
{
    public function execute(int $serviceRequestId, int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId !== $cost->service_request_id) {
            throw ApiException::badRequest('Service request id not match');
        }

        $cost->delete();
    }
}
