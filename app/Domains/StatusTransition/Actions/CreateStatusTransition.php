<?php

namespace App\Domains\StatusTransition\Actions;

use App\Models\StatusTransition;

class CreateStatusTransition
{
    public function execute(array $data): StatusTransition
    {
        return StatusTransition::create([
            'code' => $data['code'],
            'from_status_id' => $data['from_status_id'],
            'to_status_id' => $data['to_status_id'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
