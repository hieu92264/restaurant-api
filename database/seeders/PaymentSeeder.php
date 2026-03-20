<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\PaymentMethod;
use App\Common\Constants\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashier = User::where('user_name', 'cashier')->first();
        $invoice = Invoice::withoutGlobalScopes()->where('no', 'INV-20260320-000001')->first();

        Payment::withoutGlobalScopes()->updateOrCreate(
            [
                'invoice_id' => $invoice?->id,
                'transaction_code' => 'CASH-INV-000001',
            ],
            [
                'method' => PaymentMethod::CASH,
                'qr_content' => null,
                'requested_amount' => 92000,
                'paid_amount' => 100000,
                'paid_time' => '2026-03-20 12:35:00',
                'status' => PaymentStatus::SUCCESS,
                'confirmed_by_employee_id' => $cashier?->id,
                'note' => 'Thanh toan tien mat va tra lai 8.000',
                'is_active' => ActiveStatus::YES,
            ]
        );
    }
}
