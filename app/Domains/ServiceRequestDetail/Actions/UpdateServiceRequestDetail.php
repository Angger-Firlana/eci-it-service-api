<?php

namespace App\Domains\ServiceRequestDetail\Actions;

use App\Models\ServiceRequestDetail;

class UpdateServiceRequestDetail{
    public function execute(int $id, array $data):ServiceRequestDetail {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        $serviceRequestDetail->update(array_filter($data));
        return $serviceRequestDetail;
    }
}
