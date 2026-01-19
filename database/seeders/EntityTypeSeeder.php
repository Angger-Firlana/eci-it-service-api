<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EntityType;

class EntityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $entityTypes = [
            ['code' => 'SERVICE_REQUEST', 'name' => 'Service Request'],
            ['code' => 'VENDOR_APPROVAL', 'name' => 'Vendor Approval'],
            ['code' => 'INVOICE', 'name' => 'Invoice'],
        ];

        foreach ($entityTypes as $entityType) {
            EntityType::firstOrCreate(['code' => $entityType['code']], $entityType);
        }
    }
}
