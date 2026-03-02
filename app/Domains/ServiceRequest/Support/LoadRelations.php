<?php

namespace App\Domains\ServiceRequest\Support;

use App\Domains\ServiceRequest\Support\ShowRelationsHandler;
use App\Models\ServiceRequest;

class LoadRelations{
    protected ShowRelationsHandler $showRelationsHandler;

    public function __construct(ShowRelationsHandler $showRelationsHandler)
    {
        $this->showRelationsHandler = $showRelationsHandler;
    }

    public function execute($serviceRequest):ServiceRequest
    {
        return $serviceRequest->load($this->showRelationsHandler->defaultWith());
    
    }
}