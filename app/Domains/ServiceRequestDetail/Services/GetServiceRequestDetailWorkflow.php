<?php

namespace App\Domains\ServiceRequestDetail\Services;

use App\Domains\ServiceRequestDetail\Actions\GetServiceRequestDetail;

class GetServiceRequestDetailWorkflow
{
    protected GetServiceRequestDetail $getServiceRequestDetail;

    public function __construct(GetServiceRequestDetail $getServiceRequestDetail)
    {
        $this->getServiceRequestDetail = $getServiceRequestDetail;
    }

    public function getDetailById(int $id)
    {
        return $this->getServiceRequestDetail->getDetailById($id);
    }
}
