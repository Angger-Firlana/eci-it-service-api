<?php

namespace App\Domains\ServiceRequest\Actions;
use App\Models\ServiceRequest;

class UpdateServiceRequestOperator
{
    public function execute(ServiceRequest $serviceRequest, int $operatorId): void
    {
        $serviceRequest->update(['operator_id' => $operatorId]);
    }
}