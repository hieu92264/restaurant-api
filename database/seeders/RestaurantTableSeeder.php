<?php

namespace Database\Seeders;

use App\Common\Constants\RestaurantTableStatus;
use App\Models\RestaurantTable;
use App\Models\TableArea;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            ['slug' => 'A01', 'name' => 'Bàn A01', 'area_slug' => 'AREA_1', 'capacity' => 4, 'status' => RestaurantTableStatus::AVAILABLE],
            ['slug' => 'A02', 'name' => 'Bàn A02', 'area_slug' => 'AREA_1', 'capacity' => 4, 'status' => RestaurantTableStatus::RESERVED],
            ['slug' => 'G01', 'name' => 'Bàn G01', 'area_slug' => 'GARDEN', 'capacity' => 6, 'status' => RestaurantTableStatus::AVAILABLE],
            ['slug' => 'VIP01', 'name' => 'Phòng VIP 01', 'area_slug' => 'VIP_1', 'capacity' => 10, 'status' => RestaurantTableStatus::DISABLED],
        ];

        foreach ($tables as $tableData) {
            $area = TableArea::withoutGlobalScopes()->where('slug', $tableData['area_slug'])->first();

            RestaurantTable::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $tableData['slug']],
                [
                    'area_id' => $area?->id,
                    'name' => $tableData['name'],
                    'capacity' => $tableData['capacity'],
                    'status' => $tableData['status'],
                    'is_active' => true,
                ]
            );
        }
    }
}
