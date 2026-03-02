<?php

namespace App\Domains\ServiceRequest\Support;

class Helpers
{
    public static function formatServiceNumber(int $id): string
    {
        return 'SR' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
}