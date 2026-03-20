<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\MenuItemStatus;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Database\Seeder;

class MenuItemVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [
            ['sku_code' => 'TS_TT_M', 'menu_item_code' => 'TS_TT', 'name' => 'Tra sua truyen thong - size M', 'size_code' => 'M', 'price' => 39000, 'cost_price' => 18000, 'is_default' => true, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => 45000],
            ['sku_code' => 'TS_TT_L', 'menu_item_code' => 'TS_TT', 'name' => 'Tra sua truyen thong - size L', 'size_code' => 'L', 'price' => 49000, 'cost_price' => 22000, 'is_default' => false, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => 55000],
            ['sku_code' => 'TRA_DAO_M', 'menu_item_code' => 'TRA_DAO', 'name' => 'Tra dao cam sa - size M', 'size_code' => 'M', 'price' => 45000, 'cost_price' => 19000, 'is_default' => true, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => null],
            ['sku_code' => 'COM_RANG_STD', 'menu_item_code' => 'COM_RANG_HAI_SAN', 'name' => 'Com rang hai san', 'size_code' => null, 'price' => 69000, 'cost_price' => 35000, 'is_default' => true, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => null],
            ['sku_code' => 'SUON_NUONG_STD', 'menu_item_code' => 'SUON_NUONG', 'name' => 'Suon nuong mat ong', 'size_code' => null, 'price' => 79000, 'cost_price' => 42000, 'is_default' => true, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => null],
            ['sku_code' => 'COCA_LON_STD', 'menu_item_code' => 'COCA_LON', 'name' => 'Coca cola lon', 'size_code' => null, 'price' => 18000, 'cost_price' => 9000, 'is_default' => true, 'status' => MenuItemStatus::ACTIVE, 'compare_at_price' => null],
        ];

        foreach ($variants as $variant) {
            $menuItem = MenuItem::withoutGlobalScopes()->where('code', $variant['menu_item_code'])->first();

            MenuItemVariant::withoutGlobalScopes()->updateOrCreate(
                ['sku_code' => $variant['sku_code']],
                [
                    'menu_item_id' => $menuItem?->id,
                    'name' => $variant['name'],
                    'size_code' => $variant['size_code'],
                    'price' => $variant['price'],
                    'cost_price' => $variant['cost_price'],
                    'is_default' => $variant['is_default'],
                    'status' => $variant['status'],
                    'compare_at_price' => $variant['compare_at_price'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
