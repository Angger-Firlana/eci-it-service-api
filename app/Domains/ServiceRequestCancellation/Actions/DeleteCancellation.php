<?php

namespace App\Domains\ServiceRequestCancellation\Actions;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\ServiceCancellation;
use App\Models\Status;

class DeleteCancellation
{
    public function execute(int $id): void
    {
        $cancellation = ServiceCancellation::findOrFail($id);

        $serviceRequest = $cancellation->serviceRequest;
        $serviceRequest->update([
            'status_id' => $this->getServiceRequestStatusId(ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);

        $cancellation->delete();
    }

    private function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}
