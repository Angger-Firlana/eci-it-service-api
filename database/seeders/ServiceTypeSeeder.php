<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = [
            ['name' => 'Hardware Repair'],
            ['name' => 'Software Installation'],
            ['name' => 'Network Setup'],
            ['name' => 'Data Recovery'],
            ['name' => 'System Maintenance'],
            ['name' => 'Equipment Installation'],
            ['name' => 'Troubleshooting'],
            ['name' => 'Upgrade Service'],
            ['name' => 'Security Audit'],
            ['name' => 'Backup Setup'],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::firstOrCreate(['name' => $serviceType['name']], $serviceType);
        }
    }
}
