<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\ComboDish;
use App\Models\Dish;
use Illuminate\Database\Seeder;

class ComboDishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $combo = Combo::withoutGlobalScopes()->where('slug', 'CB_TRUA_A')->first();

        $comboItems = [
            ['dish_slug' => 'com-suon-nuong', 'quantity' => 2, 'sort_order' => 1],
            ['dish_slug' => 'tra-dao-cam-sa', 'quantity' => 2, 'sort_order' => 2],
        ];

        foreach ($comboItems as $item) {
            $dish = Dish::withoutGlobalScopes()->where('slug', $item['dish_slug'])->first();

            ComboDish::withoutGlobalScopes()->updateOrCreate(
                [
                    'combo_id' => $combo?->id,
                    'dish_id' => $dish?->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
