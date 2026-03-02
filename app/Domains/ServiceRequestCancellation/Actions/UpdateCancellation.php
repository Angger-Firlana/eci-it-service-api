<?php

namespace App\Domains\ServiceRequestCancellation\Actions;

use App\Models\ServiceCancellation;

class UpdateCancellation
{
    public function execute(int $id, array $data): ServiceCancellation
    {
        $cancellation = ServiceCancellation::findOrFail($id);

        $updateData = [
            'reason' => $data['reason'] ?? $cancellation->reason,
            'canceled_by' => $data['canceled_by'] ?? $cancellation->canceled_by,
        ];

        $cancellation->update(array_filter($updateData));

        return $cancellation->load('cancelledBy');
    }
}

