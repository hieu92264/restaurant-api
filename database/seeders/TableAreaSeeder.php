<?php

namespace Database\Seeders;

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
            ['slug' => 'AREA_1', 'name' => 'Tầng 1', 'sort_order' => 1, 'is_active' => true],
            ['slug' => 'GARDEN', 'name' => 'Sân vườn', 'sort_order' => 2, 'is_active' => true],
            ['slug' => 'VIP_1', 'name' => 'Phòng VIP 1', 'sort_order' => 3, 'is_active' => true],
        ];

        foreach ($areas as $area) {
            TableArea::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $area['slug']],
                $area
            );
        }
    }
}
