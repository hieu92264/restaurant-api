<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\InvoiceStatus;
use App\Models\CartOrder;
use App\Models\Invoice;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashier = User::where('user_name', 'cashier')->first();
        $order = CartOrder::withoutGlobalScopes()->where('order_no', 'ORD20260320-0002')->first();
        $table = RestaurantTable::withoutGlobalScopes()->where('code', 'G01')->first();
        $session = TableSession::withoutGlobalScopes()->where('table_id', $table?->id)->where('opened_at', '2026-03-20 11:45:00')->first();

        Invoice::withoutGlobalScopes()->updateOrCreate(
            ['no' => 'INV-20260320-000001'],
            [
                'cart_order_id' => $order?->id,
                'session_id' => $session?->id,
                'table_id' => $table?->id,
                'created_by_employee_id' => $cashier?->id,
                'customer_name' => 'Cong ty ABC',
                'customer_phone' => '0909000001',
                'subtotal_amount' => 97000,
                'discount_amount' => 5000,
                'service_charge_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 92000,
                'paid_amount' => 100000,
                'change_amount' => 8000,
                'invoice_status' => InvoiceStatus::PAID,
                'issued_at' => '2026-03-20 12:30:00',
                'paid_at' => '2026-03-20 12:35:00',
                'note' => 'Khach thanh toan mot lan',
                'is_active' => ActiveStatus::YES,
            ]
        );
    }
}
