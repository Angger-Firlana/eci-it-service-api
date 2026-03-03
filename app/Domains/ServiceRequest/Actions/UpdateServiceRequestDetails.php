<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;

use App\Domains\ServiceRequestDetail\Services\UpdateServiceRequestDetailWorkflow;

class UpdateServiceRequestDetails
{
    private UpdateServiceRequestDetailWorkflow $detailService;

    public function __construct(UpdateServiceRequestDetailWorkflow $detailService)
    {
        $this->detailService = $detailService;
    }

    public function execute(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {

            $this->detailService->execute($serviceRequest->service_request_details->first()->id,array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }
}
