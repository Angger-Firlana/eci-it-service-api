<?php

namespace App\Domains\StatusTransition\Actions;

use App\Models\StatusTransition;

class DeleteStatusTransition
{
    public function execute(int $id): void
    {
        StatusTransition::findOrFail($id)->delete();
    }
}
