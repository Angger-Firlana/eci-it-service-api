<?php

namespace App\Domains\StatusTransition\Actions;

use App\Models\StatusTransition;

class GetStatusTransitionById
{
    public function execute(int $id): StatusTransition
    {
        return StatusTransition::with(['status', 'roles'])->findOrFail($id);
    }
}
