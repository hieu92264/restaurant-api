<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\MenuItemStatus;
use App\Models\CookingMethod;
use App\Models\ItemType;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drinkType = ItemType::withoutGlobalScopes()->where('code', 'DRINK')->first();
        $foodType = ItemType::withoutGlobalScopes()->where('code', 'FOOD')->first();

        $items = [
            [
                'code' => 'TS_TT',
                'name' => 'Tra sua truyen thong',
                'item_type_id' => $drinkType?->id,
                'category_code' => 'MILK_TEA',
                'cooking_method_code' => null,
                'base_unit' => 'ly',
                'remark' => 'Mon bestseller cua quan',
                'image_url' => null,
                'kitchen_print_name' => 'TS truyen thong',
                'is_featured' => true,
                'is_new_item' => false,
                'sort_order' => 1,
                'available_from' => '08:00:00',
                'available_to' => '22:00:00',
                'status' => MenuItemStatus::ACTIVE,
            ],
            [
                'code' => 'TRA_DAO',
                'name' => 'Tra dao cam sa',
                'item_type_id' => $drinkType?->id,
                'category_code' => 'MILK_TEA',
                'cooking_method_code' => null,
                'base_unit' => 'ly',
                'remark' => 'Tra dao thanh mat',
                'image_url' => null,
                'kitchen_print_name' => 'Tra dao',
                'is_featured' => true,
                'is_new_item' => true,
                'sort_order' => 2,
                'available_from' => '08:00:00',
                'available_to' => '22:00:00',
                'status' => MenuItemStatus::ACTIVE,
            ],
            [
                'code' => 'COM_RANG_HAI_SAN',
                'name' => 'Com rang hai san',
                'item_type_id' => $foodType?->id,
                'category_code' => 'MAIN_DISH',
                'cooking_method_code' => 'STIR_FRIED',
                'base_unit' => 'phan',
                'remark' => 'Mon chinh phuc vu bua trua',
                'image_url' => null,
                'kitchen_print_name' => 'Com rang',
                'is_featured' => false,
                'is_new_item' => false,
                'sort_order' => 1,
                'available_from' => '10:00:00',
                'available_to' => '21:30:00',
                'status' => MenuItemStatus::ACTIVE,
            ],
            [
                'code' => 'SUON_NUONG',
                'name' => 'Suon nuong mat ong',
                'item_type_id' => $foodType?->id,
                'category_code' => 'GRILLED',
                'cooking_method_code' => 'GRILLED',
                'base_unit' => 'phan',
                'remark' => 'Suon nuong an kem com',
                'image_url' => null,
                'kitchen_print_name' => 'Suon nuong',
                'is_featured' => false,
                'is_new_item' => false,
                'sort_order' => 2,
                'available_from' => '10:00:00',
                'available_to' => '21:30:00',
                'status' => MenuItemStatus::ACTIVE,
            ],
            [
                'code' => 'COCA_LON',
                'name' => 'Coca cola lon',
                'item_type_id' => $drinkType?->id,
                'category_code' => 'SOFT_DRINK',
                'cooking_method_code' => null,
                'base_unit' => 'lon',
                'remark' => 'Nuoc ngot dong lon',
                'image_url' => null,
                'kitchen_print_name' => 'Coca lon',
                'is_featured' => false,
                'is_new_item' => false,
                'sort_order' => 3,
                'available_from' => null,
                'available_to' => null,
                'status' => MenuItemStatus::ACTIVE,
            ],
        ];

        foreach ($items as $item) {
            $category = MenuCategory::withoutGlobalScopes()->where('code', $item['category_code'])->first();
            $cookingMethod = $item['cooking_method_code']
                ? CookingMethod::withoutGlobalScopes()->where('code', $item['cooking_method_code'])->first()
                : null;

            MenuItem::withoutGlobalScopes()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'item_type_id' => $item['item_type_id'],
                    'category_id' => $category?->id,
                    'cooking_method_id' => $cookingMethod?->id,
                    'name' => $item['name'],
                    'base_unit' => $item['base_unit'],
                    'remark' => $item['remark'],
                    'image_url' => $item['image_url'],
                    'kitchen_print_name' => $item['kitchen_print_name'],
                    'is_featured' => $item['is_featured'],
                    'is_new_item' => $item['is_new_item'],
                    'sort_order' => $item['sort_order'],
                    'available_from' => $item['available_from'],
                    'available_to' => $item['available_to'],
                    'status' => $item['status'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
