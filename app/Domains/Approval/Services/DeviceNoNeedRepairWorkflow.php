<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\DeviceNoNeedRepair;
use App\Models\ServiceRequest;

class DeviceNoNeedRepairWorkflow
{
    public function __construct(
        protected DeviceNoNeedRepair $deviceNoNeedRepair
    ) {
    }

    public function execute(int $serviceRequestId, array $data): ServiceRequest
    {
        return $this->deviceNoNeedRepair->execute($serviceRequestId, $data);
    }
}
