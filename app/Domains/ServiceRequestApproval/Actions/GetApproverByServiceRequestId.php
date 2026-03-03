<?php

namespace App\Domains\ServiceRequestApproval\Actions;

use App\Domains\Approval\Actions\GetApproverByServiceRequestId as ResolveApproverByServiceRequestId;

class GetApproverByServiceRequestId
{
    public function __construct(
        protected ResolveApproverByServiceRequestId $resolveApproverByServiceRequestId
    ) {
    }

    public function execute(int $serviceRequestId): array
    {
        return $this->resolveApproverByServiceRequestId->execute($serviceRequestId);
    }
}
