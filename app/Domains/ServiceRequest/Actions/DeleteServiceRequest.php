<?php
namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;

class DeleteServiceRequest{
    public function execute(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->loadMissing('status');
        if ($serviceRequest->status?->code === ServiceRequestStatusCode::COMPLETED->value) {
            throw new \Exception('Cannot delete completed service request');
        }

        $serviceRequest->delete();

        return $serviceRequest;
    }
}