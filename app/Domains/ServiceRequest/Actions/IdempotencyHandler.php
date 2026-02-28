<?php

namespace App\Domains\ServiceRequest\Actions;
use App\Models\ServiceRequest;
use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;

class IdempotencyHandler
{
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;

    public function __construct(EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest)
    {
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
    }

    public function execute(ServiceRequest $serviceRequest, array $details): void{
        $this->ensureDeviceIsNotActiveInOtherRequest->execute($details, $serviceRequest->id);
    }
}