<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $technicianRole = Role::where('name', 'technician')->first();
        $superiorRole = Role::where('name', 'superior')->first();

        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@eci-service.com',
                'password' => Hash::make('admin123'),
                'pin' => '1234',
                'roles' => [$adminRole]
            ],
            [
                'name' => 'John Doe',
                'email' => 'john.doe@company.com',
                'password' => Hash::make('user123'),
                'pin' => '5678',
                'roles' => [$userRole]
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@company.com',
                'password' => Hash::make('user123'),
                'pin' => '9012',
                'roles' => [$userRole]
            ],
            [
                'name' => 'Tech Wilson',
                'email' => 'tech.wilson@eci-service.com',
                'password' => Hash::make('tech123'),
                'pin' => '3456',
                'roles' => [$technicianRole]
            ],
            [
                'name' => 'Service Brown',
                'email' => 'service.brown@eci-service.com',
                'password' => Hash::make('tech123'),
                'pin' => '7890',
                'roles' => [$technicianRole]
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@company.com',
                'password' => Hash::make('atasan123'),
                'pin' => '2468',
                'roles' => [$superiorRole]
            ],
        ];

        foreach ($users as $userData) {
            $roles = $userData['roles'];
            unset($userData['roles']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            
            foreach ($roles as $role) {
                $user->roles()->syncWithoutDetaching($role->id);
            }
        }
    }
}
