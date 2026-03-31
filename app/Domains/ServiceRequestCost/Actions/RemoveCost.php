<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Domains\ServiceRequestCost\Support\CostAttachmentStorage;
use App\Exceptions\ApiException;
use App\Models\ServiceCost;

class RemoveCost
{
    public function __construct(
        protected CostAttachmentStorage $attachmentStorage
    ) {
    }

    public function execute(int $serviceRequestId, int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId !== $cost->service_request_id) {
            throw ApiException::badRequest('Service request id not match');
        }

        $this->attachmentStorage->delete($cost->image_path);
        $cost->delete();
    }
}
