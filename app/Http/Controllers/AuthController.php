<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Helpers\APIResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

/**
 * Handles authentication-related API requests such as login, registration, and logout.
 */
class AuthController extends Controller
{
    protected $authService;
    protected $apiResponse;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService The authentication service instance.
     * @param APIResponse $apiResponse The API response helper instance.
     */
    public function __construct(
        AuthService $authService,
        APIResponse $apiResponse
    ) {
        $this->authService = $authService;
        $this->apiResponse = $apiResponse;

    }

    /**
     * Handle a login request.
     *
     * @param LoginRequest $request The login request containing user credentials.
     * @return \Illuminate\Http\JsonResponse The JSON response with token or error.
     */
    public function login(LoginRequest $request){
        $result = $this->authService->login($request->validated());
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    /**
     * Handle a user registration request.
     *
     * @param RegisterRequest $request The registration request containing user data.
     * @return \Illuminate\Http\JsonResponse The JSON response with the newly registered user or error.
     */
    public function register(RegisterRequest $request){
        $result = $this->authService->register($request->validated());
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    /**
     * Handle a user logout request.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating logout success or error.
     */
    public function logout(){
        $result = $this->authService->logout();
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }

    /**
     * Get data of the currently authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response with user data or error.
     */
    public function getDataMe(){
        $result = $this->authService->getDataUserByToken();
        if(!$result['code'] >= 200 && $result['code'] < 300){
            return $this->apiResponse->error($result['errors'] ?? null, $result['code'], $result['message']);
        }

        return $this->apiResponse->success($result['data'] ?? null, $result['code'], $result['message']);
    }
}
