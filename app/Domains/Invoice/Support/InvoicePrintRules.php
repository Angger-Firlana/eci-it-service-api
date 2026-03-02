<?php

namespace App\Domains\Invoice\Support;

use App\Enums\ServiceRequestStatusCode;

class InvoicePrintRules
{
    public const BLOCKED_STATUS_CODES = [
        ServiceRequestStatusCode::CANCELLED->value,
    ];

    public static function isBlocked(?string $statusCode): bool
    {
        return in_array($statusCode, self::BLOCKED_STATUS_CODES, true);
    }
}
