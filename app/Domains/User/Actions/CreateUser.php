<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin' => $data['pin'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['department_id'])) {
            $user->departments()->sync([$data['department_id']]);
        }

        if (isset($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
        }

        return $user->load('departments', 'roles');
    }
}
