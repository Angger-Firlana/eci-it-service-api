<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Domains\Approval\Services\GetApproverByServiceRequestWorkflow;

class GetApproverByServiceRequestId
{
    public function __construct(
        protected GetApproverByServiceRequestWorkflow $getApproverByServiceRequestWorkflow
    ) {
    }

    public function execute(int $serviceRequestId): array
    {
        return $this->getApproverByServiceRequestWorkflow->execute($serviceRequestId);
    }
}

