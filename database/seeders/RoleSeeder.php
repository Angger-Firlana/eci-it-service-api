<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin'],
            ['name' => 'operator'],
            ['name' => 'user'],
            ['name' => 'technician'],
            ['name' => 'supervisor'],
            ['name' => 'manager'],
            ['name' => 'director'],
            ['name' => 'ceo']
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
