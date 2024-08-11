<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            ['name' => 'party', 'image' => 'storage/images/Untitled.jpeg'],
            ['name' => 'wadding', 'image' => 'storage/images/Untitled.jpeg'],
            ['name' => 'Birthday', 'image' => 'storage/images/Untitled.jpeg'],
            ['name' => 'Graduation', 'image' => 'storage/images/Untitled.jpeg'],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::create([
                'name' => $serviceType['name'],
                'image' => $serviceType['image']
            ]);
        }
    }
}
