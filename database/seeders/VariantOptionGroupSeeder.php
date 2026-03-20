<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Models\MenuItemVariant;
use App\Models\OptionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class VariantOptionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            ['variant_sku' => 'TS_TT_M', 'group_code' => 'SUGAR_LEVEL', 'display_order' => 1],
            ['variant_sku' => 'TS_TT_M', 'group_code' => 'ICE_LEVEL', 'display_order' => 2],
            ['variant_sku' => 'TS_TT_M', 'group_code' => 'TOPPING', 'display_order' => 3],
            ['variant_sku' => 'TS_TT_L', 'group_code' => 'SUGAR_LEVEL', 'display_order' => 1],
            ['variant_sku' => 'TS_TT_L', 'group_code' => 'ICE_LEVEL', 'display_order' => 2],
            ['variant_sku' => 'TS_TT_L', 'group_code' => 'TOPPING', 'display_order' => 3],
            ['variant_sku' => 'TRA_DAO_M', 'group_code' => 'SUGAR_LEVEL', 'display_order' => 1],
            ['variant_sku' => 'TRA_DAO_M', 'group_code' => 'ICE_LEVEL', 'display_order' => 2],
        ];

        foreach ($links as $link) {
            $variant = MenuItemVariant::withoutGlobalScopes()->where('sku_code', $link['variant_sku'])->first();
            $group = OptionGroup::withoutGlobalScopes()->where('code', $link['group_code'])->first();

            if (!$variant || !$group) {
                continue;
            }

            DB::table('variant_option_groups')->updateOrInsert(
                [
                    'variant_id' => $variant->id,
                    'option_group_id' => $group->id,
                ],
                [
                    'display_order' => $link['display_order'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
