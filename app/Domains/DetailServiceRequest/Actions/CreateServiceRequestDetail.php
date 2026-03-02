<?php

namespace App\Domains\DetailServiceRequest\Actions;

use App\Models\ServiceRequestDetail;

class CreateServiceRequestDetail{
    public function execute(array $data):ServiceRequestDetail{
        $serviceRequestDetail = ServiceRequestDetail::create([
            'service_request_id' => $data['service_request_id'],
            'service_type_id' => $data['service_type_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'complaint' => $data['complaint'],
        ]);
        
        return $serviceRequestDetail;
    }
}