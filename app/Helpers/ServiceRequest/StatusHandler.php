<?php

namespace App\Helpers\ServiceRequest;

use App\Enums\ServiceRequestStatusCode;

class StatusHandler
{
    public static function handle($serviceRequest,$currentStatus, $statusCode, $notificationService, $invoiceService, $detailService):void
    {
        logger()->info("Handling status change for ServiceRequest ID: {$serviceRequest->id}, New Status Code: {$statusCode}");
        // TODO: Implement status handling logic
        if ($statusCode === ServiceRequestStatusCode::BAD_ASSET->value) {
            $detailService->markDevicesAsBadAsset($serviceRequest);
        }

        if($statusCode === ServiceRequestStatusCode::COMPLETED->value || $statusCode === ServiceRequestStatusCode::BAD_ASSET->value || $statusCode === ServiceRequestStatusCode::CANCELLED->value){
            $notificationService->createNotificationForServiceRequest($serviceRequest, $currentStatus);
        }
    }
}