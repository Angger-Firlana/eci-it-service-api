<?php

namespace App\Domains\ServiceRequest\Support;

use App\Exceptions\ApiException;
use App\Models\Status;

class GetServiceRequestStatusById
{
    public function execute(int $statusId): Status
    {
        $status = Status::with('entity_type')->findOrFail($statusId);

        if ($status->entity_type?->code !== 'SERVICE_REQUEST') {
            throw ApiException::badRequest('Status tidak valid');
        }

        return $status;
    }
}
