<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name'=>'desserts'],
            ['name'=>'Cakes'],
            ['name'=>'Grills'],
            ['name'=>'Pastry']
        ];
        DB::table('food_category')->insert($data);
    }
}
