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
            DeviceTypeSeeder::class,
            DeviceModelSeeder::class,
            ServiceTypeSeeder::class,
            ConditionTypeDataSeeder::class,
            ConditionTypeSeeder::class,
            ApprovalPolicySeeder::class,
            VendorSeeder::class,
            UserSeeder::class,
        ]);
    }
}
