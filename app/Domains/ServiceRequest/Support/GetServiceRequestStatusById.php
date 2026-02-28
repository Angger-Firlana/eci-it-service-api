<?php

namespace App\Domains\ServiceRequest\Support;

use App\Models\Status;

class GetStatusById
{
    private function execute(int $statusId): Status
    {
        $status = Status::with('entity_type')->findOrFail($statusId);

        if ($status->entity_type?->code !== 'SERVICE_REQUEST') {
            throw new \Exception('Status tidak valid');
        }

        return $status;
    }
}