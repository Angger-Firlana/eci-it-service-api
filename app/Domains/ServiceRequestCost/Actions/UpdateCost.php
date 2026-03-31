<?php

namespace App\Domains\ServiceRequestCost\Actions;

use App\Domains\ServiceRequestCost\Support\CostAttachmentStorage;
use App\Exceptions\ApiException;
use App\Models\ServiceCost;

class UpdateCost
{
    public function __construct(
        protected CostAttachmentStorage $attachmentStorage
    ) {
    }

    public function execute(int $serviceRequestId, int $costId, array $data): ServiceCost
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId != $cost->service_request_id) {
            throw ApiException::badRequest('Service request id not match');
        }

        if (isset($data['image'])) {
            $this->attachmentStorage->delete($cost->image_path);
            $data['image_path'] = $this->attachmentStorage->store($serviceRequestId, $data['image']);
        }

        $cost->update([
            'cost_type_id' => $data['cost_type_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
        ]);

        return $cost->load('cost_type');
    }
}
