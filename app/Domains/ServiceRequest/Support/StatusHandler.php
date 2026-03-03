<?php

namespace App\Domains\ServiceRequest\Support;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;

class StatusHandler
{
    public static function handle($serviceRequest,$currentStatus, $statusCode, $notificationService, $invoiceService, $detailService):void
    {
        // TODO: Implement status handling logic
        if ($statusCode === ServiceRequestStatusCode::BAD_ASSET->value) {
            $detailService->markDevicesAsBadAsset($serviceRequest);
        }

        if($statusCode === ServiceRequestStatusCode::COMPLETED->value || $statusCode === ServiceRequestStatusCode::BAD_ASSET->value || $statusCode === ServiceRequestStatusCode::CANCELLED->value){
            $notificationService->createNotificationForServiceRequest($serviceRequest, $currentStatus);
        }
    }
}
