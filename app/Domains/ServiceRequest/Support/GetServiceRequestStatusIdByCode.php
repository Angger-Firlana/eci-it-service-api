<?php

namespace App\Domains\ServiceRequest\GenerateServiceNumber;

use App\Models\ServiceRequest;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\Status;

class GetServiceRequestStatusIdByCode{
    private static function execute(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}