<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Helpers\APIResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    //
    protected $authService;
    protected $apiResponse;

    public function __construct(
        AuthService $authService,
        APIResponse $apiResponse
    ) {
        $this->authService = $authService;
        $this->apiResponse = $apiResponse;

    }

    public function login(LoginRequest $request){
        $result = $this->authService->login($request->validated());
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    public function register(RegisterRequest $request){
        $result = $this->authService->register($request->validated());
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    public function logout(){
        $result = $this->authService->logout();
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    public function getDataMe(){
        $result = $this->authService->getDataUserByToken();
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }
}
