<?php

namespace App\Domains\Invoice\Actions;

use App\Domains\Invoice\Support\InvoicePrintRules;
use App\Models\ServiceRequest;

class CanPrintInvoice
{
    public function execute(int $serviceRequestId): bool
    {
        $serviceRequest = ServiceRequest::with('status')->find($serviceRequestId);
        if (!$serviceRequest) {
            return false;
        }

        $statusCode = $serviceRequest->status?->code;
        return !InvoicePrintRules::isBlocked($statusCode);
    }
}
