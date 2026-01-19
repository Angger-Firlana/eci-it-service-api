<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): array{
        $user = User::where('email', $credentials['email'])->first();

        if(!$user || !Hash::check($credentials['password'], $user->password)){
            return ['success' => false, 'message' => 'Invalid credentials', 'code' => 401];
        }

        $token = $user->createToken('token_eci_service' . now()->timestamp)->plainTextToken;

        return ['success' => true, 'message' => 'Login successful', 'data' => ['user' => $user, 'token' => $token], 'code' => 200];
    }

    public function register(array $data): array{
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin' => isset($data['pin']) ? Hash::make($data['pin']) : null,
        ]);

        $token = $user->createToken('token_eci_service' . now()->timestamp)->plainTextToken;

        return ['success' => true, 'message' => 'Registration successful', 'data' => ['user' => $user, 'token' => $token], 'code' => 201];
    }

    public function getDataUserByToken(string $token): ?User{
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        return $tokenModel ? $tokenModel->tokenable : null;
    }

    public function logout(User $user): array{
        $user->tokens()->delete();

        if(!$user->tokens()->count()){
            return ['success' => false, 'message' => 'Logout failed', 'code' => 500];
        }
        
        return ['success' => true, 'message' => 'Logout successful', 'code' => 200];
    }
}