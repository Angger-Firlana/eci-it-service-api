<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceModel;
use App\Models\DeviceType;

class DeviceModelSeeder extends Seeder
{
    public function run(): void
    {
        $laptopType = DeviceType::where('name', 'Laptop')->first();
        $desktopType = DeviceType::where('name', 'Desktop')->first();
        $printerType = DeviceType::where('name', 'Printer')->first();
        $monitorType = DeviceType::where('name', 'Monitor')->first();

        $deviceModels = [
            ['device_type_id' => $laptopType->id, 'brand' => 'Dell', 'model' => 'Latitude 5420'],
            ['device_type_id' => $laptopType->id, 'brand' => 'HP', 'model' => 'EliteBook 840 G8'],
            ['device_type_id' => $laptopType->id, 'brand' => 'Lenovo', 'model' => 'ThinkPad T14'],
            ['device_type_id' => $laptopType->id, 'brand' => 'Apple', 'model' => 'MacBook Pro 14"'],
            ['device_type_id' => $desktopType->id, 'brand' => 'Dell', 'model' => 'OptiPlex 7090'],
            ['device_type_id' => $desktopType->id, 'brand' => 'HP', 'model' => 'EliteDesk 800 G6'],
            ['device_type_id' => $desktopType->id, 'brand' => 'Lenovo', 'model' => 'ThinkCentre M70q'],
            ['device_type_id' => $printerType->id, 'brand' => 'HP', 'model' => 'LaserJet Pro M404n'],
            ['device_type_id' => $printerType->id, 'brand' => 'Canon', 'model' => 'imageCLASS MF244dw'],
            ['device_type_id' => $printerType->id, 'brand' => 'Brother', 'model' => 'HL-L2350DW'],
            ['device_type_id' => $monitorType->id, 'brand' => 'Dell', 'model' => 'UltraSharp U2422H'],
            ['device_type_id' => $monitorType->id, 'brand' => 'HP', 'model' => 'EliteDisplay E243'],
            ['device_type_id' => $monitorType->id, 'brand' => 'LG', 'model' => '27UL850-W'],
        ];

        foreach ($deviceModels as $deviceModel) {
            DeviceModel::firstOrCreate([
                'device_type_id' => $deviceModel['device_type_id'],
                'brand' => $deviceModel['brand'],
                'model' => $deviceModel['model']
            ], $deviceModel);
        }
    }
}
