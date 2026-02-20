<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $operatorRole = Role::where('name', 'operator')->first();
        $userRole = Role::where('name', 'user')->first();
        $technicianRole = Role::where('name', 'technician')->first();
        $supervisorRole = Role::where('name', 'supervisor')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $seniorManagerRole = Role::where('name', 'senior manager')->first();
        $ceoRole = Role::where('name', 'ceo')->first();

        if (!$adminRole || !$operatorRole || !$userRole || !$technicianRole || !$supervisorRole || !$managerRole || !$seniorManagerRole || !$ceoRole) {
            throw new RuntimeException('Required roles not found. Run RoleSeeder first.');
        }

        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();
        $financeDepartment = Department::where('code', 'FIN')->first();
        $gaDepartment = Department::where('code', 'GA')->first();

        if (!$itDepartment || !$hrDepartment || !$financeDepartment || !$gaDepartment) {
            throw new RuntimeException('Required departments not found. Run DepartmentSeeder first.');
        }

        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'pin' => '1234',
                'roles' => [$adminRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'ALI',
                'email' => 'it.ali@gmail.com',
                'password' => Hash::make('ali123'),
                'pin' => '5678',
                'roles' => [$operatorRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'John Doe',
                'email' => 'john.doe@gmail.com',
                'password' => Hash::make('user123'),
                'pin' => '5678',
                'roles' => [$userRole],
                'departments' => [$hrDepartment]
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@gmail.com',
                'password' => Hash::make('user123'),
                'pin' => '9012',
                'roles' => [$userRole],
                'departments' => [$financeDepartment]
            ],
            [
                'name' => 'Tech Wilson',
                'email' => 'tech.wilson@gmail.com',
                'password' => Hash::make('tech123'),
                'pin' => '3456',
                'roles' => [$technicianRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'Service Brown',
                'email' => 'service.brown@gmail.com',
                'password' => Hash::make('tech123'),
                'pin' => '7890',
                'roles' => [$technicianRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@gmail.com',
                'password' => Hash::make('atasan123'),
                'pin' => '2468',
                'roles' => [$supervisorRole],
                'departments' => [$gaDepartment]
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('manager123'),
                'pin' => '1111',
                'roles' => [$managerRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'Senior Manager',
                'email' => 'senior.manager@gmail.com',
                'password' => Hash::make('manager123'),
                'pin' => '2222',
                'roles' => [$seniorManagerRole],
                'departments' => [$itDepartment]
            ],
            [
                'name' => 'CEO',
                'email' => 'ceo@gmail.com',
                'password' => Hash::make('ceo123'),
                'pin' => '3333',
                'roles' => [$ceoRole],
                'departments' => [$itDepartment]
            ],
        ];

        foreach ($users as $userData) {
            $roles = $userData['roles'];
            $departments = $userData['departments'] ?? [];
            unset($userData['roles']);
            unset($userData['departments']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            
            foreach ($roles as $role) {
                if($role) {
                    $user->roles()->syncWithoutDetaching($role->id);
                }
            }

            foreach ($departments as $department) {
                if($department) {
                    $user->departments()->syncWithoutDetaching($department->id);
                }
            }
        }
    }
}
