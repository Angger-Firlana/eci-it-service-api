<?php

namespace App\Domains\ServiceRequestLocation\Actions;

use App\Models\ServiceLocation;

class GetServiceLocationById
{
    public function execute(int $id): ServiceLocation
    {
        return ServiceLocation::with(['service_request', 'vendor'])->findOrFail($id);
    }
}

