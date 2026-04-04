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
        $comboItemsBySlug = [
            'da-tiec-thuong-uyen' => [
                ['dish_slug' => 'com-suon-nuong', 'quantity' => 2, 'sort_order' => 1],
                ['dish_slug' => 'mi-xao-hai-san', 'quantity' => 1, 'sort_order' => 2],
                ['dish_slug' => 'tra-dao-cam-sa', 'quantity' => 2, 'sort_order' => 3],
                ['dish_slug' => 'che-khuc-bach', 'quantity' => 2, 'sort_order' => 4],
            ],
            'huong-vi-dai-duong' => [
                ['dish_slug' => 'mi-xao-hai-san', 'quantity' => 2, 'sort_order' => 1],
                ['dish_slug' => 'bun-bo-hue', 'quantity' => 2, 'sort_order' => 2],
                ['dish_slug' => 'tra-dao-cam-sa', 'quantity' => 2, 'sort_order' => 3],
                ['dish_slug' => 'che-khuc-bach', 'quantity' => 2, 'sort_order' => 4],
            ],
            'business-lunch' => [
                ['dish_slug' => 'com-suon-nuong', 'quantity' => 1, 'sort_order' => 1],
                ['dish_slug' => 'ca-phe-sua-da', 'quantity' => 1, 'sort_order' => 2],
                ['dish_slug' => 'tra-dao-cam-sa', 'quantity' => 1, 'sort_order' => 3],
                ['dish_slug' => 'banh-flan', 'quantity' => 1, 'sort_order' => 4],
            ],
            'afternoon-tea-set' => [
                ['dish_slug' => 'tra-sua-truyen-thong', 'quantity' => 2, 'sort_order' => 1],
                ['dish_slug' => 'tra-dao-cam-sa', 'quantity' => 1, 'sort_order' => 2],
                ['dish_slug' => 'banh-flan', 'quantity' => 2, 'sort_order' => 3],
                ['dish_slug' => 'che-khuc-bach', 'quantity' => 2, 'sort_order' => 4],
            ],
        ];

        foreach ($comboItemsBySlug as $comboSlug => $items) {
            $combo = Combo::withoutGlobalScopes()->where('slug', $comboSlug)->first();

            if ($combo === null) {
                continue;
            }

            foreach ($items as $item) {
                $dish = Dish::withoutGlobalScopes()->where('slug', $item['dish_slug'])->first();

                if ($dish === null) {
                    continue;
                }

                ComboDish::withoutGlobalScopes()->updateOrCreate(
                    [
                        'combo_id' => $combo->id,
                        'dish_id' => $dish->id,
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
}
