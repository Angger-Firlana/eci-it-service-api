<?php

namespace App\Domains\ServiceRequest\Actions;


use App\Models\ServiceRequest;

use App\Domains\DetailServiceRequest\Services\CreateDetailServiceRequestWorkflow;

class CreateServiceRequestDetails{
    private CreateDetailServiceRequestWorkflow $detailService;

    public function __construct(CreateDetailServiceRequestWorkflow $detailService)
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