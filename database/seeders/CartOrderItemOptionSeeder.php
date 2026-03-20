<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\CartOrderItemOption;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use Illuminate\Database\Seeder;

class CartOrderItemOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = CartOrder::withoutGlobalScopes()->where('order_no', 'ORD20260320-0001')->first();
        $line = CartOrderItem::withoutGlobalScopes()
            ->where('cart_order_id', $order?->id)
            ->where('item_name_snapshot', 'Tra sua truyen thong')
            ->first();

        $options = [
            ['group_code' => 'SUGAR_LEVEL', 'value_code' => 'SUGAR_50'],
            ['group_code' => 'ICE_LEVEL', 'value_code' => 'ICE_50'],
            ['group_code' => 'TOPPING', 'value_code' => 'PUDDING'],
        ];

        foreach ($options as $option) {
            $group = OptionGroup::withoutGlobalScopes()->where('code', $option['group_code'])->first();
            $value = OptionValue::withoutGlobalScopes()
                ->where('option_group_id', $group?->id)
                ->where('value_code', $option['value_code'])
                ->first();

            CartOrderItemOption::withoutGlobalScopes()->updateOrCreate(
                [
                    'cart_order_item_id' => $line?->id,
                    'option_value_id' => $value?->id,
                ],
                [
                    'option_group_id' => $group?->id,
                    'option_group_name_snapshot' => $group?->name,
                    'option_value_name_snapshot' => $value?->value_name,
                    'price_delta_snapshot' => $value?->price_delta ?? 0,
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}
