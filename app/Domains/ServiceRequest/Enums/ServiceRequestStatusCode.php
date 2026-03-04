<?php

namespace App\Domains\ServiceRequest\Enums;

enum ServiceRequestStatusCode: string
{
    case REVIEW_IN_WORKSHOP = 'REVIEW_IN_WORKSHOP';
    case REPAIR_IN_WORKSHOP = 'REPAIR_IN_WORKSHOP';
    case REPAIR_IN_VENDOR = 'REPAIR_IN_VENDOR';
    case WAITING_APPROVAL_ABOVE = 'WAITING_APPROVAL_ABOVE';
    case REJECTED_BY_ABOVE = 'REJECTED_BY_ABOVE';
    case COMPLETED = 'COMPLETED';
    case BAD_ASSET = 'BAD_ASSET';
    case CANCELLED = 'CANCELLED';
}
