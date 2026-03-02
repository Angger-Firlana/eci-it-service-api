<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Domains\User\Services\UserService;
use App\Helpers\APIResponse;

class UserController extends Controller
{
    //
    protected $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    public function index(Request $request){
        $paginator = $this->userService->getAllUser($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "", $meta);
    }

    public function show($id){
        $user = $this->userService->getUserById($id);
        return APIResponse::success($user);
    }

    public function store(StoreUserRequest $request){
        $user = $this->userService->createUser($request->validated());
        return APIResponse::success($user);
    }

    public function update(UpdateUserRequest $request, $id){
        $user = $this->userService->updateUser($id, $request->validated());
        return APIResponse::success($user);
    }

    public function destroy($id){
        $user = $this->userService->deleteUser($id);
        return APIResponse::success($user);
    }
}
