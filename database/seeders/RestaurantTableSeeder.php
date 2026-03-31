<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
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
            ['code' => 'A01', 'name' => 'Ban A01', 'area_code' => 'AREA_1', 'capacity' => 4, 'status' => RestaurantTableStatus::AVAILABLE],
            ['code' => 'A02', 'name' => 'Ban A02', 'area_code' => 'AREA_1', 'capacity' => 4, 'status' => RestaurantTableStatus::RESERVED],
            ['code' => 'G01', 'name' => 'Ban G01', 'area_code' => 'GARDEN', 'capacity' => 6, 'status' => RestaurantTableStatus::AVAILABLE],
            ['code' => 'VIP01', 'name' => 'Phong VIP 01', 'area_code' => 'VIP_1', 'capacity' => 10, 'status' => RestaurantTableStatus::DISABLED],
        ];

        foreach ($tables as $tableData) {
            $area = TableArea::withoutGlobalScopes()->where('code', $tableData['area_code'])->first();

            RestaurantTable::withoutGlobalScopes()->updateOrCreate(
                ['code' => $tableData['code']],
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
