<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Models\ComboGroup;
use App\Models\ComboGroupItem;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Database\Seeder;

class ComboGroupItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['group_name' => 'Chon mon chinh', 'menu_item_code' => 'COM_RANG_HAI_SAN', 'variant_sku' => 'COM_RANG_STD', 'extra_price' => 0, 'is_default' => true],
            ['group_name' => 'Chon mon chinh', 'menu_item_code' => 'SUON_NUONG', 'variant_sku' => 'SUON_NUONG_STD', 'extra_price' => 15000, 'is_default' => false],
            ['group_name' => 'Chon nuoc uong', 'menu_item_code' => 'COCA_LON', 'variant_sku' => 'COCA_LON_STD', 'extra_price' => 0, 'is_default' => true],
            ['group_name' => 'Chon nuoc uong', 'menu_item_code' => 'TRA_DAO', 'variant_sku' => 'TRA_DAO_M', 'extra_price' => 5000, 'is_default' => false],
        ];

        foreach ($items as $item) {
            $group = ComboGroup::withoutGlobalScopes()->where('group_name', $item['group_name'])->first();
            $menuItem = MenuItem::withoutGlobalScopes()->where('code', $item['menu_item_code'])->first();
            $variant = MenuItemVariant::withoutGlobalScopes()->where('sku_code', $item['variant_sku'])->first();

            ComboGroupItem::withoutGlobalScopes()->updateOrCreate(
                [
                    'combo_group_id' => $group?->id,
                    'menu_item_id' => $menuItem?->id,
                ],
                [
                    'variant_id' => $variant?->id,
                    'extra_price' => $item['extra_price'],
                    'is_default' => $item['is_default'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
