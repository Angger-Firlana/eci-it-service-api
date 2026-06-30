<?php

namespace App\Domains\SetEmailIt\Actions;

use App\Models\SetEmailIt;

class UpdateSetEmailIt
{
    public function execute(int $id, array $data): SetEmailIt
    {
        $record = SetEmailIt::findOrFail($id);

        $record->update([
            'is_active' => $data['is_active'],
        ]);

        return $record->load('user:id,name,username,email');
    }
}
