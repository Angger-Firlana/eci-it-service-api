<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\DeviceNeedRepair;
use App\Models\ServiceRequest;

class DeviceNeedRepairWorkflow
{
    public function __construct(
        protected DeviceNeedRepair $deviceNeedRepair
    ) {
    }

    public function execute(int $serviceRequestId, array $data): ServiceRequest
    {
        return $this->deviceNeedRepair->execute($serviceRequestId, $data);
    }
}
