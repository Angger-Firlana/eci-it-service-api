<?php

namespace App\Enums;

enum InvoiceStatusCode: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
}
