<?php

namespace App\Domains\ServiceRequestDetail\Services;

use App\Domains\ServiceRequestDetail\Actions\DeleteServiceRequestDetail;


class DeleteServiceRequestDetailWorkflow
{
    protected DeleteServiceRequestDetail $deleteServiceRequestDetail;

    public function __construct(DeleteServiceRequestDetail $deleteServiceRequestDetail)
    {
        $this->deleteServiceRequestDetail = $deleteServiceRequestDetail;
    }

    public function delete(int $id): void
    {
        $this->deleteServiceRequestDetail->deleteDetailServiceRequest($id);
    }
}
