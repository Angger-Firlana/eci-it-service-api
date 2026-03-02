<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use App\Domains\DetailServiceRequest\CreateServiceRequestDetailWorkFlow;
class SyncDetails{
    protected CreateServiceRequestDetailWorkFlow $createServiceRequestDetailWorkFlow;

    public function __construct(CreateServiceRequestDetailWorkFlow $createServiceRequestDetailWorkFlow)
    {
        $this->createServiceRequestDetailWorkFlow = $createServiceRequestDetailWorkFlow;
    }

    public function execute(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {
            if (isset($detail['id'])) {
                continue;
            }

            $this->createServiceRequestDetailWorkFlow->execute(array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }
}