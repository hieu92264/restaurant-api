<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Models\ItemType;
use Illuminate\Database\Seeder;

class ItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemTypes = [
            ['code' => 'FOOD', 'name' => 'Do an'],
            ['code' => 'DRINK', 'name' => 'Do uong'],
            ['code' => 'COMBO', 'name' => 'Combo'],
            ['code' => 'TOPPING', 'name' => 'Topping'],
            ['code' => 'SURCHARGE', 'name' => 'Phu thu'],
        ];

        foreach ($itemTypes as $itemType) {
            ItemType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $itemType['code']],
                $itemType + ['is_active' => ActiveStatus::YES]
            );
        }
    }
}
