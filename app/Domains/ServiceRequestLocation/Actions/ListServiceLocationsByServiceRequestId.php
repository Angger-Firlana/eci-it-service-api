<?php

namespace App\Domains\ServiceRequestLocation\Actions;

use App\Models\ServiceLocation;
use Illuminate\Database\Eloquent\Collection;

class ListServiceLocationsByServiceRequestId
{
    public function execute(int $serviceRequestId): Collection
    {
        return ServiceLocation::with('vendor')
            ->where('service_request_id', $serviceRequestId)
            ->get();
    }
}

