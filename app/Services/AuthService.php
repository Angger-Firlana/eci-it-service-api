<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Service class for handling authentication-related operations.
 */
class AuthService
{
    /**
     * Authenticate a user and generate an access token.
     *
     * @param array $credentials The user login credentials, including 'email' and 'password'.
     * @return array An associative array containing 'success', 'message', 'data' (user and token), and 'code'.
     */
    public function login(array $credentials): array{
        $user = User::where('email', $credentials['email'])->with(['roles:id,name'])->first();

        if(!$user || !Hash::check($credentials['password'], $user->password)){
            return ['success' => false, 'message' => 'Invalid credentials', 'code' => 401];
        }

        $token = $user->createToken('token_eci_service' . now()->timestamp)->plainTextToken;

        return ['success' => true, 'message' => 'Login successful', 'data' => ['user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->select('id', 'name')->first(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ], 'token' => $token], 'code' => 200];
    }

    /**
     * Register a new user.
     *
     * @param array $data The user registration data, including 'name', 'email', 'password', and optional 'pin' and 'role_id'.
     * @return array An associative array containing 'success', 'message', 'data' (newly created user and token), and 'code'.
     */
    public function register(array $data): array{
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin' => isset($data['pin']) ? Hash::make($data['pin']) : null,
            'role_id' => $data['role_id'] ?? 3
        ]);

        $user->load(['roles:id,name']);

        $token = $user->createToken('token_eci_service' . now()->timestamp)->plainTextToken;

        return ['success' => true, 'message' => 'Registration successful', 'data' => ['user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->select('id', 'name')->first(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ], 'token' => $token], 'code' => 201];
    }

    /**
     * Retrieve data for the currently authenticated user based on their token.
     *
     * @return array|null An associative array containing 'success', 'message', 'data' (user information), and 'code', or null if no user is authenticated.
     */
    public function getDataUserByToken(): ?array{
        $user = auth()->user();
        if(!$user){
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }
        $user->load(['roles:id,name']);
        return ['success' => true, 'message' => 'User found', 'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->select('id', 'name')->first(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ], 'code' => 200];
    }

    /**
     * Log out the currently authenticated user by revoking their tokens.
     *
     * @param User $user The authenticated user instance.
     * @return array An associative array containing 'success', 'message', and 'code'.
     */
    public function logout(User $user): array{
        $user->tokens()->delete();

        if(!$user->tokens()->count()){
            return ['success' => false, 'message' => 'Logout failed', 'code' => 500];
        }
        
        return ['success' => true, 'message' => 'Logout successful', 'code' => 200];
    }
}