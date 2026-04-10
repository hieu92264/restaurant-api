<?php

namespace Database\Seeders;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\CartOrder;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartOrderSeeder extends Seeder
{
    public function run(): void
    {
        $waiter = User::withoutGlobalScopes()->where('user_name', 'waiter')->first();
        $tableA01 = RestaurantTable::withoutGlobalScopes()->where('slug', 'A01')->first();
        $tableG01 = RestaurantTable::withoutGlobalScopes()->where('slug', 'G01')->first();

        $openSession = TableSession::withoutGlobalScopes()
            ->where('table_id', $tableA01?->id)
            ->where('status', TableSessionStatus::OPEN)
            ->latest('opened_at')
            ->first();

        $closedSession = TableSession::withoutGlobalScopes()
            ->where('table_id', $tableG01?->id)
            ->where('status', TableSessionStatus::CLOSED)
            ->latest('opened_at')
            ->first();

        $orders = [
            [
                'order_no' => 'ORD20260320-0001',
                'session_id' => $openSession?->id,
                'table_id' => $tableA01?->id,
                'created_by_employee' => $waiter?->user_name,
                'status' => CartOrderStatus::OPEN,
                'subtotal_amount' => 126000,
                'discount_amount' => 0,
                'service_charge_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 126000,
                'remark' => 'Ban dang phuc vu',
            ],
            [
                'order_no' => 'ORD20260320-0002',
                'session_id' => $closedSession?->id,
                'table_id' => $tableG01?->id,
                'created_by_employee' => $waiter?->user_name,
                'status' => CartOrderStatus::CONVERTED_TO_INVOICE,
                'subtotal_amount' => 97000,
                'discount_amount' => 5000,
                'service_charge_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 92000,
                'remark' => 'Da chuyen sang hoa don',
            ],
        ];

        foreach ($orders as $order) {
            CartOrder::withoutGlobalScopes()->updateOrCreate(
                ['order_no' => $order['order_no']],
                $order + ['is_active' => true]
            );
        }
    }
}
