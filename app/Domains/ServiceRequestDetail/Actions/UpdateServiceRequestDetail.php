<?php

namespace App\Domains\ServiceRequestDetail\Actions;

use App\Models\ServiceRequestDetail;

class UpdateServiceRequestDetail{
    public function execute(ServiceRequestDetail $serviceRequestDetail, array $data):ServiceRequestDetail {
        $serviceRequestDetail->update(array_filter($data));
        return $serviceRequestDetail;
    }
}
