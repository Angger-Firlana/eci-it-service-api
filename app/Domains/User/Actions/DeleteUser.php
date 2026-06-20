<?php

namespace App\Domains\User\Actions;

use App\Models\User;

class DeleteUser
{
    public function execute(int $id): void
    {
        $user = User::findOrFail($id);

        // Revoke all Sanctum tokens so the account can no longer be accessed
        // with previously issued credentials once it is (soft) deleted.
        $user->tokens()->delete();

        // Soft delete: keeps the row (and data referencing it) intact while
        // excluding the user from all default queries and authentication.
        $user->delete();
    }
}
