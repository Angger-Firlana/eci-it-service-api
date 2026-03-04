<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Domains\User\Actions\CreateUser;
use App\Domains\User\Actions\DeleteUser;
use App\Domains\User\Actions\GetUserById;
use App\Domains\User\Actions\ListUsers;
use App\Domains\User\Actions\UpdateUser;
use App\Helpers\APIResponse;

class UserController extends Controller
{
    protected ListUsers $listUsers;
    protected GetUserById $getUserById;
    protected CreateUser $createUser;
    protected UpdateUser $updateUser;
    protected DeleteUser $deleteUser;

    public function __construct(
        ListUsers $listUsers,
        GetUserById $getUserById,
        CreateUser $createUser,
        UpdateUser $updateUser,
        DeleteUser $deleteUser
    ){
        $this->listUsers = $listUsers;
        $this->getUserById = $getUserById;
        $this->createUser = $createUser;
        $this->updateUser = $updateUser;
        $this->deleteUser = $deleteUser;
    }
    public function index(Request $request){
        $paginator = $this->listUsers->execute($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "", $meta);
    }

    public function show($id){
        $user = $this->getUserById->execute((int) $id);
        return APIResponse::success($user);
    }

    public function store(StoreUserRequest $request){
        $user = $this->createUser->execute($request->validated());
        return APIResponse::success($user);
    }

    public function update(UpdateUserRequest $request, $id){
        $user = $this->updateUser->execute((int) $id, $request->validated());
        return APIResponse::success($user);
    }

    public function destroy($id){
        $user = $this->deleteUser->execute((int) $id);
        return APIResponse::success($user);
    }
}
