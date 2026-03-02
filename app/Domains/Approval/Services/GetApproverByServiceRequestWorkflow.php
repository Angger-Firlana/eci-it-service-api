<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\GetApproverByServiceRequestId;

class GetApproverByServiceRequestWorkflow
{
    public function __construct(
        protected GetApproverByServiceRequestId $getApproverByServiceRequestId
    ) {
    }

    public function execute(int $serviceRequestId): array
    {
        return $this->getApproverByServiceRequestId->execute($serviceRequestId);
    }
}
