<?php

namespace App\Domains\User\Actions;

use App\Models\User;

class DeleteUser
{
    public function execute(int $id): void
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
