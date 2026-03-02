<?php

namespace App\Domains\DetailServiceRequest\Services;

use App\Domains\DetailServiceRequest\Actions\GetServiceRequestDetail;

class GetServiceRequestDetailServiceWorkflow{
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