<?php

namespace App\Domains\ServiceRequest\Actions;


use App\Models\ServiceRequest;

use App\Domains\ServiceRequestDetail\Services\CreateServiceRequestDetailWorkflow;

class CreateServiceRequestDetails
{
    private CreateServiceRequestDetailWorkflow $detailService;

    public function __construct(CreateServiceRequestDetailWorkflow $detailService)
    {
        $this->detailService = $detailService;
    }

    public function execute(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {
            $this->detailService->execute(array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }
}
