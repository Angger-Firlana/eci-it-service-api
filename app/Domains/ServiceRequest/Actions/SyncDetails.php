<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use App\Domains\ServiceRequestDetail\Services\CreateServiceRequestDetailWorkflow;

class SyncDetails
{
    protected CreateServiceRequestDetailWorkflow $createServiceRequestDetailWorkflow;

    public function __construct(CreateServiceRequestDetailWorkflow $createServiceRequestDetailWorkflow)
    {
        $this->createServiceRequestDetailWorkflow = $createServiceRequestDetailWorkflow;
    }

    public function execute(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {
            if (isset($detail['id'])) {
                continue;
            }

            $this->createServiceRequestDetailWorkflow->execute(array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }
}
