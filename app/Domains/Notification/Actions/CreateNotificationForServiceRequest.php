<?php

namespace App\Domains\Notification\Actions;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\ServiceRequest;
use App\Models\Status;

class CreateNotificationForServiceRequest
{
    public function __construct(
        protected CreateNotification $createNotification
    ) {
    }

    public function execute(ServiceRequest $serviceRequest, Status $status): void
    {
        $data = [
            'service_request_id' => $serviceRequest->id,
            'status_code' => $status->code,
            'user_id' => $serviceRequest->user_id,
        ];

        if ($status->code === ServiceRequestStatusCode::COMPLETED->value) {
            $data['title'] = "Service Request #{$serviceRequest->service_number} Completed";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been completed. Please check the details for more information.";
        } elseif ($status->code === ServiceRequestStatusCode::BAD_ASSET->value) {
            $data['title'] = "Service Request #{$serviceRequest->service_number} Marked as Bad Asset";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been marked as bad asset. Please contact support for more information.";
        } elseif ($status->code === ServiceRequestStatusCode::CANCELLED->value) {
            $data['title'] = "Service Request #{$serviceRequest->service_number} Cancelled";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been cancelled. Please contact support for more information.";
        }

        $this->createNotification->execute($data);
    }
}
