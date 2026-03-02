<?php

namespace App\Domains\ServiceRequestLocation\Actions;

use App\Models\ServiceLocation;

class DeleteServiceLocation
{
    public function execute(int $id): void
    {
        $serviceLocation = ServiceLocation::findOrFail($id);
        $serviceLocation->delete();
    }
}

