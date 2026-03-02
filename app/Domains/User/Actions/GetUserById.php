<?php

namespace App\Domains\User\Actions;

use App\Models\User;

class GetUserById
{
    public function execute(int $id): User
    {
        return User::with('departments', 'roles')->findOrFail($id);
    }
}
