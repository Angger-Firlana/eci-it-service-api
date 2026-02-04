<?php

namespace App\Enums;

enum VendorApprovalStatusCode: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
}
