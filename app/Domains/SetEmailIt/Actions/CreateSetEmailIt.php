<?php

namespace App\Domains\SetEmailIt\Actions;

use App\Models\SetEmailIt;

class CreateSetEmailIt
{
    public function execute(array $data): SetEmailIt
    {
        $record = SetEmailIt::create([
            'user_id' => $data['user_id'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $record->load('user:id,name,username,email');
    }
}
