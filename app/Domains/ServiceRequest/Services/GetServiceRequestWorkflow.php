<?php

namespace App\Domains\ServiceRequest\Services;

use App\Domains\ServiceRequest\Actions\GetServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\ServiceRequest;
    
class GetServiceRequestWorkflow{
    protected GetServiceRequest $getServiceRequest;
    public function __construct(GetServiceRequest $getServiceRequest)
    {
        $this->getServiceRequest = $getServiceRequest;
    }

    public function getServiceRequestById(int $id):ServiceRequest
    {
        return $this->getServiceRequest->getServiceRequestById($id);
    }

    public function getAllServiceRequest($request):LengthAwarePaginator
    {
        return $this->getServiceRequest->getAllServiceRequest($request);
    }

    public function getStats():array
    {
        return $this->getServiceRequest->getStats();
    }
}