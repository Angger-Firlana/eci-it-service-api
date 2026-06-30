<?php

namespace App\Domains\ServiceRequest\Actions;

use Illuminate\Support\Facades\Auth;
use App\Models\ServiceRequest;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Exceptions\ApiException;

use App\Domains\ServiceRequest\Support\GenerateServiceNumber;
use App\Domains\ServiceRequest\Support\GetServiceRequestStatusIdByCode;

class CreateMainServiceRequest{
    public function execute($data)
    {
        $operatorId = null;
        $userId = null;
        $user = Auth::user();
      
        // Users can only create requests for themselves.
        if (isset($data['user_id']) && (int) $data['user_id'] !== (int) $user->id) {
            throw ApiException::forbidden('You can only create a service request for your own account.');
        }

        $userId = $user->id;
        
        return ServiceRequest::create([
            'service_number'   => GenerateServiceNumber::execute(),
            'operator_id'         => $operatorId,
            'user_id'          => $userId,
            'status_id'        => GetServiceRequestStatusIdByCode::execute(ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);
    }
}
