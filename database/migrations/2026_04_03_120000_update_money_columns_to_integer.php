<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->changeMoneyColumnsToInteger();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->changeMoneyColumnsToDecimal();
        }
    }

    private function changeMoneyColumnsToInteger(): void
    {
        if (Schema::hasTable('combos')) {
            DB::statement('ALTER TABLE combos MODIFY discount_price INTEGER NOT NULL');
        }

        if (Schema::hasTable('cart_orders')) {
            DB::statement('ALTER TABLE cart_orders MODIFY subtotal_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY discount_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY service_charge_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY tax_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY total_amount INTEGER NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('cart_order_items')) {
            DB::statement('ALTER TABLE cart_order_items MODIFY base_unit_price INTEGER NOT NULL');
            DB::statement('ALTER TABLE cart_order_items MODIFY option_total_price INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_order_items MODIFY unit_final_price INTEGER NOT NULL');
            DB::statement('ALTER TABLE cart_order_items MODIFY line_total INTEGER NOT NULL');
        }

        if (Schema::hasTable('invoices')) {
            DB::statement('ALTER TABLE invoices MODIFY subtotal_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY discount_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY service_charge_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY tax_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY total_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY paid_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY change_amount INTEGER NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('invoice_items')) {
            DB::statement('ALTER TABLE invoice_items MODIFY base_unit_price INTEGER NOT NULL');
            DB::statement('ALTER TABLE invoice_items MODIFY option_total_price INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoice_items MODIFY unit_final_price INTEGER NOT NULL');
            DB::statement('ALTER TABLE invoice_items MODIFY line_total INTEGER NOT NULL');
        }

        if (Schema::hasTable('payments')) {
            DB::statement('ALTER TABLE payments MODIFY requested_amount INTEGER NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE payments MODIFY paid_amount INTEGER NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('discounts')) {
            DB::statement('ALTER TABLE discounts MODIFY min_order_value INTEGER NULL');
        }
    }

    private function changeMoneyColumnsToDecimal(): void
    {
        if (Schema::hasTable('combos')) {
            DB::statement('ALTER TABLE combos MODIFY discount_price DECIMAL(12,2) NOT NULL');
        }

        if (Schema::hasTable('cart_orders')) {
            DB::statement('ALTER TABLE cart_orders MODIFY subtotal_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY service_charge_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_orders MODIFY total_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('cart_order_items')) {
            DB::statement('ALTER TABLE cart_order_items MODIFY base_unit_price DECIMAL(12,2) NOT NULL');
            DB::statement('ALTER TABLE cart_order_items MODIFY option_total_price DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE cart_order_items MODIFY unit_final_price DECIMAL(12,2) NOT NULL');
            DB::statement('ALTER TABLE cart_order_items MODIFY line_total DECIMAL(12,2) NOT NULL');
        }

        if (Schema::hasTable('invoices')) {
            DB::statement('ALTER TABLE invoices MODIFY subtotal_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY service_charge_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY total_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoices MODIFY change_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('invoice_items')) {
            DB::statement('ALTER TABLE invoice_items MODIFY base_unit_price DECIMAL(12,2) NOT NULL');
            DB::statement('ALTER TABLE invoice_items MODIFY option_total_price DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE invoice_items MODIFY unit_final_price DECIMAL(12,2) NOT NULL');
            DB::statement('ALTER TABLE invoice_items MODIFY line_total DECIMAL(12,2) NOT NULL');
        }

        if (Schema::hasTable('payments')) {
            DB::statement('ALTER TABLE payments MODIFY requested_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE payments MODIFY paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('discounts')) {
            DB::statement('ALTER TABLE discounts MODIFY min_order_value DECIMAL(8,2) NULL');
        }
    }
};
