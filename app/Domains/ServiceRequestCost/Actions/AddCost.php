<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Domains\ServiceRequestCost\Support\CostAttachmentStorage;
use App\Models\ServiceCost;

class AddCost
{
    public function __construct(
        protected CostAttachmentStorage $attachmentStorage
    ) {
    }

    public function execute(int $serviceRequestId, array $data): ServiceCost
    {
        $data['service_request_id'] = $serviceRequestId;

        if (isset($data['image'])) {
            $data['image_path'] = $this->attachmentStorage->store($serviceRequestId, $data['image']);
        }

        $serviceCost = ServiceCost::create($data);

        return $serviceCost->load('cost_type');
    }
}

