<?php

namespace Database\Seeders;

use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\PaymentMethod;
use App\Common\Constants\TableSessionStatus;
use App\Models\CartOrder;
use App\Models\Invoice;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::withoutGlobalScopes()->where('user_name', 'cashier')->first();
        $order = CartOrder::withoutGlobalScopes()->where('order_no', 'ORD20260320-0002')->first();
        $table = RestaurantTable::withoutGlobalScopes()->where('slug', 'G01')->first();
        $session = TableSession::withoutGlobalScopes()
            ->where('table_id', $table?->id)
            ->where('status', TableSessionStatus::CLOSED)
            ->latest('opened_at')
            ->first();

        $issuedAt = now()->copy()->subDay()->setTime(12, 30);
        $paidAt = now()->copy()->subDay()->setTime(12, 35);

        Invoice::withoutGlobalScopes()->updateOrCreate(
            ['no' => 'INV-20260320-000001'],
            [
                'cart_order_id' => $order?->id,
                'session_id' => $session?->id,
                'table_id' => $table?->id,
                'reservation_code' => null,
                'created_by_employee' => $cashier?->user_name,
                'customer_name' => 'Cong ty ABC',
                'customer_phone' => '0909000001',
                'subtotal_amount' => 97000,
                'discount_amount' => 5000,
                'service_charge_amount' => 0,
                'tax_amount' => 0,
                'deposit_amount' => 0,
                'total_amount' => 92000,
                'paid_amount' => 100000,
                'remaining_amount' => 0,
                'change_amount' => 8000,
                'payment_method' => PaymentMethod::CASH,
                'payment_status' => InvoicePaymentStatus::PAID,
                'issued_at' => $issuedAt,
                'paid_at' => $paidAt,
                'note' => 'Khach thanh toan mot lan',
                'is_active' => true,
            ]
        );
    }
}
