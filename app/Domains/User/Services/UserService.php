<?php

namespace App\Domains\User\Services;

use App\Domains\User\Actions\CreateUser;
use App\Domains\User\Actions\DeleteUser;
use App\Domains\User\Actions\GetUserById;
use App\Domains\User\Actions\ListUsers;
use App\Domains\User\Actions\UpdateUser;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class UserService
{
    public function __construct(
        protected ListUsers $listUsers,
        protected GetUserById $getUserById,
        protected CreateUser $createUser,
        protected UpdateUser $updateUser,
        protected DeleteUser $deleteUser
    ) {
    }

    public function getAllUser(Request $request): LengthAwarePaginator
    {
        return $this->listUsers->execute($request);
    }

    public function getUserById(int $id): User
    {
        return $this->getUserById->execute($id);
    }

    public function createUser(array $data): User
    {
        return $this->createUser->execute($data);
    }

    public function updateUser(int $id, array $data): User
    {
        return $this->updateUser->execute($id, $data);
    }

    public function deleteUser(int $id): void
    {
        $this->deleteUser->execute($id);
    }
}
