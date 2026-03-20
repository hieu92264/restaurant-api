<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Models\TableArea;
use Illuminate\Database\Seeder;

class TableAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['code' => 'AREA_1', 'name' => 'Tang 1', 'sort_order' => 1, 'is_active' => ActiveStatus::YES],
            ['code' => 'GARDEN', 'name' => 'San vuon', 'sort_order' => 2, 'is_active' => ActiveStatus::YES],
            ['code' => 'VIP_1', 'name' => 'Phong VIP 1', 'sort_order' => 3, 'is_active' => ActiveStatus::YES],
        ];

        foreach ($areas as $area) {
            TableArea::withoutGlobalScopes()->updateOrCreate(
                ['code' => $area['code']],
                $area
            );
        }
    }
}
