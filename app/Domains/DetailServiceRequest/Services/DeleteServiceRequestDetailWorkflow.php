<?php

namespace App\Domains\DetailServiceRequest\Services;

use App\Domains\DetailServiceRequest\Actions\DeleteServiceRequestDetail;


class DeleteServiceRequestDetailWorkflow{
    protected DeleteServiceRequestDetail $deleteServiceRequestDetail;

    public function __construct(DeleteServiceRequestDetail $deleteServiceRequestDetail)
    {
        $this->deleteServiceRequestDetail = $deleteServiceRequestDetail;
    }

    public function delete(int $id)
    {
        return $this->deleteServiceRequestDetail->deleteDetailServiceRequest($id);
    }
}