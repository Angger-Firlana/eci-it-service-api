<?php

namespace App\Domains\DetailServiceRequest\Actions;

use App\Models\ServiceRequestDetail;

class GetServiceRequestDetail{
    public function getDetailById(int $id): ServiceRequestDetail
    {
        $detail = ServiceRequestDetail::with('device')->findOrFail($id);
        return $detail;
    }
}