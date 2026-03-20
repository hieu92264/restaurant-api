<?php

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('no', 30)->unique();
            $table->foreignId('cart_order_id')->constrained('cart_orders');
            $table->foreignId('session_id')->constrained('table_sessions');
            $table->foreignId('table_id')->constrained('restaurant_tables');
            $table->foreignId('created_by_employee_id')->constrained('users');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('service_charge_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->enum('invoice_status', InvoiceStatus::values())
                ->default(InvoiceStatus::PENDING);
            $table->dateTime('issued_at');
            $table->dateTime('paid_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
