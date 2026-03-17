<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\Role;
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

        $isOperator = $user->roles->contains('id', Role::OPERATOR);
        $isUser = $user->roles->contains('id', Role::USER);

        if ($isOperator) {
            // Operators can create on behalf of another user.
            $operatorId = $user->id;
            $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        } elseif ($isUser) {
            // Users can only create requests for themselves.
            if (isset($data['user_id']) && (int) $data['user_id'] !== (int) $user->id) {
                throw ApiException::forbidden('You can only create a service request for your own account.');
            }

            $userId = $user->id;
        } else {
            throw ApiException::forbidden('Your role is not allowed to create service requests.');
        }

        return ServiceRequest::create([
            'service_number'   => GenerateServiceNumber::execute(),
            'operator_id'         => $operatorId,
            'user_id'          => $userId,
            'status_id'        => GetServiceRequestStatusIdByCode::execute(ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);
    }
}
