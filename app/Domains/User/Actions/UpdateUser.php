<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    public function execute(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $updateData = [
            'name' => $data['name'] ?? $user->name,
            'username' => $data['username'] ?? $user->username,
            'email' => $data['email'] ?? $user->email,
        ];

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['pin'])) {
            $updateData['pin'] = $data['pin'];
        }

        if (array_key_exists('is_active', $data)) {
            $updateData['is_active'] = $data['is_active'];
        }

        $user->update($updateData);

        if (isset($data['department_id'])) {
            $user->departments()->sync([$data['department_id']]);
        }

        if (isset($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
        }

        $user->save();

        return $user->load('departments', 'roles');
    }
}
