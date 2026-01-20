<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConditionTypeData;

class ConditionTypeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditionTypeData = [
            ['type_data' => 'Device Type'],
            ['type_data' => 'Service Type'],
            ['type_data' => 'Cost Range'],
        ];

        foreach ($conditionTypeData as $data) {
            ConditionTypeData::firstOrCreate(['type_data' => $data['type_data']], $data);
        }
    }
}
