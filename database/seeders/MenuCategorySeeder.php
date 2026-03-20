<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Models\ItemType;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodType = ItemType::withoutGlobalScopes()->where('code', 'FOOD')->first();
        $drinkType = ItemType::withoutGlobalScopes()->where('code', 'DRINK')->first();
        $toppingType = ItemType::withoutGlobalScopes()->where('code', 'TOPPING')->first();

        $categories = [
            ['code' => 'FOOD_ROOT', 'name' => 'Do an', 'item_type_id' => $foodType?->id, 'parent_code' => null, 'sort_order' => 1],
            ['code' => 'MAIN_DISH', 'name' => 'Mon chinh', 'item_type_id' => $foodType?->id, 'parent_code' => 'FOOD_ROOT', 'sort_order' => 1],
            ['code' => 'GRILLED', 'name' => 'Mon nuong', 'item_type_id' => $foodType?->id, 'parent_code' => 'FOOD_ROOT', 'sort_order' => 2],
            ['code' => 'DRINK_ROOT', 'name' => 'Do uong', 'item_type_id' => $drinkType?->id, 'parent_code' => null, 'sort_order' => 2],
            ['code' => 'MILK_TEA', 'name' => 'Tra sua', 'item_type_id' => $drinkType?->id, 'parent_code' => 'DRINK_ROOT', 'sort_order' => 1],
            ['code' => 'SOFT_DRINK', 'name' => 'Nuoc dong chai', 'item_type_id' => $drinkType?->id, 'parent_code' => 'DRINK_ROOT', 'sort_order' => 2],
            ['code' => 'TOPPING_ROOT', 'name' => 'Topping', 'item_type_id' => $toppingType?->id, 'parent_code' => null, 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            $parent = $category['parent_code']
                ? MenuCategory::withoutGlobalScopes()->where('code', $category['parent_code'])->first()
                : null;

            MenuCategory::withoutGlobalScopes()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'parent_id' => $parent?->id,
                    'item_type_id' => $category['item_type_id'],
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
