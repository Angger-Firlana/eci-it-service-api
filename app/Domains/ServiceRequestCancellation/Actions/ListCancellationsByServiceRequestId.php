<?php

namespace App\Domains\ServiceRequestCancellation\Actions;

use App\Models\ServiceCancellation;
use Illuminate\Database\Eloquent\Collection;

class ListCancellationsByServiceRequestId
{
    public function execute(int $serviceRequestId): Collection
    {
        return ServiceCancellation::with('cancelledBy')
            ->where('service_request_id', $serviceRequestId)
            ->get();
    }
}

