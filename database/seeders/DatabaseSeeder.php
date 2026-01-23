<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            EntityTypeSeeder::class,
            StatusSeeder::class,
            StatusTransitionSeeder::class,
            DeviceTypeSeeder::class,
            DeviceModelSeeder::class,
            ServiceTypeSeeder::class,
            CostTypeSeeder::class,
            ConditionTypeDataSeeder::class,
            ConditionTypeSeeder::class,
            ApprovalPolicySeeder::class,
            VendorSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
        ]);
    }
}
