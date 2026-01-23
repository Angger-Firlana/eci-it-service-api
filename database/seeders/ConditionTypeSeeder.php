<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConditionType;
use App\Models\ConditionTypeData;
use RuntimeException;

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

        if (!$deviceTypeData) {
            throw new RuntimeException('ConditionTypeData "Device Type" not found. Run ConditionTypeDataSeeder first.');
        }

        if (!$serviceTypeData) {
            throw new RuntimeException('ConditionTypeData "Service Type" not found. Run ConditionTypeDataSeeder first.');
        }

        if (!$costRangeData) {
            throw new RuntimeException('ConditionTypeData "Cost Range" not found. Run ConditionTypeDataSeeder first.');
        }

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
