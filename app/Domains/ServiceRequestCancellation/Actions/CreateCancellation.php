<?php

namespace App\Domains\ServiceRequestCancellation\Actions;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\ServiceCancellation;
use App\Models\ServiceRequest;
use App\Models\Status;

class CreateCancellation
{
    public function execute(array $data, ServiceRequest $serviceRequest): ServiceCancellation
    {
        $cancellation = ServiceCancellation::create([
            'service_request_id' => $serviceRequest->id,
            'reason' => $data['reason'],
            'cancelled_by' => $data['cancelled_by'] ?? auth()->id(),
        ]);

        $serviceRequest->update([
            'status_id' => $this->getServiceRequestStatusId(ServiceRequestStatusCode::CANCELLED),
        ]);

        return $cancellation->load('cancelledBy');
    }

    private function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}
