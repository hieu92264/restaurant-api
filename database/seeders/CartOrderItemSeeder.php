<?php

namespace Database\Seeders;

use App\Common\Constants\OrderLineStatus;
use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\Combo;
use App\Models\Dish;
use Illuminate\Database\Seeder;

class CartOrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'order_no' => 'ORD20260320-0001',
                'combo_slug' => null,
                'item_name_snapshot' => 'Trà sữa truyền thống',
                'variant_name_snapshot' => 'Ly lớn',
                'quantity' => 1,
                'base_unit_price' => 49000,
                'option_total_price' => 8000,
                'unit_final_price' => 57000,
                'line_total' => 57000,
                'item_note' => '50% đường, 50% đá',
                'line_status' => OrderLineStatus::ACTIVE,
                'created_at' => '2026-03-20 18:35:00',
            ],
            [
                'order_no' => 'ORD20260320-0001',
                'combo_slug' => null,
                'item_name_snapshot' => 'Cơm rang hải sản',
                'variant_name_snapshot' => null,
                'quantity' => 1,
                'base_unit_price' => 69000,
                'option_total_price' => 0,
                'unit_final_price' => 69000,
                'line_total' => 69000,
                'item_note' => 'Ít hành',
                'line_status' => OrderLineStatus::ACTIVE,
                'created_at' => '2026-03-20 18:36:00',
            ],
            [
                'order_no' => 'ORD20260320-0002',
                'combo_slug' => null,
                'item_name_snapshot' => 'Trà đào cam sả',
                'variant_name_snapshot' => 'Size M',
                'quantity' => 1,
                'base_unit_price' => 45000,
                'option_total_price' => 0,
                'unit_final_price' => 45000,
                'line_total' => 45000,
                'item_note' => null,
                'line_status' => OrderLineStatus::SERVED,
                'created_at' => '2026-03-20 11:50:00',
            ],
            [
                'order_no' => 'ORD20260320-0002',
                'combo_slug' => null,
                'item_name_snapshot' => 'Coca Cola lon',
                'variant_name_snapshot' => null,
                'quantity' => 2,
                'base_unit_price' => 18000,
                'option_total_price' => 0,
                'unit_final_price' => 18000,
                'line_total' => 36000,
                'item_note' => null,
                'line_status' => OrderLineStatus::SERVED,
                'created_at' => '2026-03-20 11:51:00',
            ],
            [
                'order_no' => 'ORD20260320-0002',
                'combo_slug' => 'CB_TRUA_A',
                'item_name_snapshot' => 'Combo trưa 2 người',
                'variant_name_snapshot' => 'Cơm rang hải sản',
                'quantity' => 1,
                'base_unit_price' => 16000,
                'option_total_price' => 0,
                'unit_final_price' => 16000,
                'line_total' => 16000,
                'item_note' => 'Bổ sung trong combo',
                'line_status' => OrderLineStatus::SERVED,
                'created_at' => '2026-03-20 11:52:00',
            ],
        ];

        foreach ($items as $item) {
            $order = CartOrder::withoutGlobalScopes()->where('order_no', $item['order_no'])->first();
            $combo = $item['combo_slug']
                ? Combo::withoutGlobalScopes()->where('slug', $item['combo_slug'])->first()
                : null;
            $dish = $combo === null
                ? Dish::withoutGlobalScopes()->where('name', $item['item_name_snapshot'])->first()
                : null;

            CartOrderItem::withoutGlobalScopes()->updateOrCreate(
                [
                    'cart_order_id' => $order?->id,
                    'item_name_snapshot' => $item['item_name_snapshot'],
                    'created_at' => $item['created_at'],
                ],
                [
                    'dish_id' => $dish?->id,
                    'combo_id' => $combo?->id,
                    'variant_name_snapshot' => $item['variant_name_snapshot'],
                    'quantity' => $item['quantity'],
                    'base_unit_price' => $item['base_unit_price'],
                    'option_total_price' => $item['option_total_price'],
                    'unit_final_price' => $item['unit_final_price'],
                    'line_total' => $item['line_total'],
                    'item_note' => $item['item_note'],
                    'line_status' => $item['line_status'],
                    'is_active' => true,
                ]
            );
        }
    }
}
