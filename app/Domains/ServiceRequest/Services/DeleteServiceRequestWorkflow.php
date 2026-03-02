<?php

namespace App\Domains\ServiceRequest\Services;

use App\Domains\ServiceRequest\Actions\DeleteServiceRequest;

class DeleteServiceRequestWorkflow{
    protected $deleteServiceRequest;

    public function __construct(
        DeleteServiceRequest $deleteServiceRequest
    ){
        $this->deleteServiceRequest = $deleteServiceRequest;
    }
    public function execute($id){
        $this->deleteServiceRequest->execute($id);
    }
}