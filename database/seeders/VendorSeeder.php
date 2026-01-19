<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Tech Solutions Inc.',
                'maps_url' => 'https://maps.google.com/?q=123+Tech+Street+Silicon+Valley+CA',
                'description' => 'Leading technology solutions provider specializing in hardware and software services.'
            ],
            [
                'name' => 'Hardware Pro Services',
                'maps_url' => 'https://maps.google.com/?q=456+Hardware+Ave+Tech+City+CA',
                'description' => 'Professional hardware repair and maintenance services for all types of IT equipment.'
            ],
            [
                'name' => 'Network Experts LLC',
                'maps_url' => 'https://maps.google.com/?q=789+Network+Blvd+Digital+Park+CA',
                'description' => 'Specialized network setup, configuration, and troubleshooting services.'
            ],
            [
                'name' => 'Software Masters',
                'maps_url' => 'https://maps.google.com/?q=321+Software+Lane+Code+Town+CA',
                'description' => 'Expert software installation, configuration, and support services.'
            ],
            [
                'name' => 'IT Support Plus',
                'maps_url' => 'https://maps.google.com/?q=654+Support+Road+Help+City+CA',
                'description' => 'Comprehensive IT support and maintenance solutions for businesses.'
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['name' => $vendor['name']], $vendor);
        }
    }
}
