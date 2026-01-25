<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CostType;

class CostTypeSeeder extends Seeder
{
    public function run(): void
    {
        $costTypes = [
            ['code' => 'SPAREPART', 'name' => 'Sparepart'],
            ['code' => 'SERVICE_FEE', 'name' => 'Service Fee'],
            ['code' => 'CANCELLATION', 'name' => 'Cancellation'],
            ['code' => 'TRANSPORT', 'name' => 'Transport'],
            ['code' => 'OTHER', 'name' => 'Other'],
        ];

        foreach ($costTypes as $costType) {
            CostType::firstOrCreate(
                ['code' => $costType['code']],
                $costType
            );
        }
    }
}
