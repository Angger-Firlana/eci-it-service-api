<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConditionType;
use App\Models\ConditionTypeData;

class ConditionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deviceTypeData = ConditionTypeData::where('type_data', 'Device Type')->first();
        $serviceTypeData = ConditionTypeData::where('type_data', 'Service Type')->first();
        $costRangeData = ConditionTypeData::where('type_data', 'Cost Range')->first();

        $conditionTypes = [
            [
                'condition_type_data_id' => $deviceTypeData->id,
                'name' => 'Device Type',
                'code' => 'DEVICE_TYPE'
            ],
            [
                'condition_type_data_id' => $serviceTypeData->id,
                'name' => 'Service Type',
                'code' => 'SERVICE_TYPE'
            ],
            [
                'condition_type_data_id' => $costRangeData->id,
                'name' => 'Cost Range',
                'code' => 'COST_RANGE'
            ],
        ];

        foreach ($conditionTypes as $conditionType) {
            ConditionType::firstOrCreate(['code' => $conditionType['code']], $conditionType);
        }
    }
}
