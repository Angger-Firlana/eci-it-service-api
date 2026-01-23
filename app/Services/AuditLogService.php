<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public function createAuditLog(array $data): AuditLog
    {
        return AuditLog::create($data);
    }
}
