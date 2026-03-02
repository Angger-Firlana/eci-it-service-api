<?php

namespace App\Domains\ServiceRequest\DTOs;

class UpdateServiceRequestData
{
    public function __construct(
        public readonly ?array $details = null,
        public readonly ?int $statusId = null,
        public readonly ?int $operatorId = null,
        public readonly ?string $logNotes = null,
    ) {}
}