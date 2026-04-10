<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['slug' => 'A01', 'name' => 'Ban A01', 'capacity' => 4],
            ['slug' => 'A02', 'name' => 'Ban A02', 'capacity' => 4],
            ['slug' => 'G01', 'name' => 'Ban G01', 'capacity' => 6],
            ['slug' => 'VIP01', 'name' => 'Phong VIP 01', 'capacity' => 10],
        ];

        foreach ($tables as $tableData) {
            RestaurantTable::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $tableData['slug']],
                [
                    'name' => $tableData['name'],
                    'capacity' => $tableData['capacity'],
                    'is_active' => true,
                ]
            );
        }
    }
}
