<?php

namespace App\Domains\SetEmailIt\Actions;

use App\Models\SetEmailIt;

class DeleteSetEmailIt
{
    public function execute(int $id): void
    {
        $record = SetEmailIt::findOrFail($id);
        $record->delete();
    }
}
