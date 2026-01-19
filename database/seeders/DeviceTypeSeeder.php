<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceType;

class DeviceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $deviceTypes = [
            ['name' => 'Laptop'],
            ['name' => 'Desktop'],
            ['name' => 'Printer'],
            ['name' => 'Scanner'],
            ['name' => 'Monitor'],
            ['name' => 'Server'],
            ['name' => 'Router'],
            ['name' => 'Switch'],
            ['name' => 'Mobile Phone'],
            ['name' => 'Tablet'],
        ];

        foreach ($deviceTypes as $deviceType) {
            DeviceType::firstOrCreate(['name' => $deviceType['name']], $deviceType);
        }
    }
}
