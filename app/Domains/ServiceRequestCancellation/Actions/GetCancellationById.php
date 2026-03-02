<?php

namespace App\Domains\ServiceRequestCancellation\Actions;

use App\Models\ServiceCancellation;

class GetCancellationById
{
    public function execute(int $id): ServiceCancellation
    {
        return ServiceCancellation::with(['serviceRequest', 'cancelledBy'])
            ->findOrFail($id);
    }
}

