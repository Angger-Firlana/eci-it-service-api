<?php

namespace App\Domains\AuditLog\Actions;

use App\Models\AuditLog;

class CreateAuditLog
{
    public function execute(array $data): AuditLog
    {
        return AuditLog::create($data);
    }
}
