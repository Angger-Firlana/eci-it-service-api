<?php

namespace App\Domains\ServiceRequest\Support;

use App\Models\ServiceRequest;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\Status;

class GetServiceRequestStatusIdByCode{
    public static function execute(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}