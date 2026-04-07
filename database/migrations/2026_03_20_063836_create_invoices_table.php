<?php

use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\PaymentMethod;
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
            $table->string('reservation_code')->nullable();
            $table->string('created_by_employee');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->integer('subtotal_amount')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('service_charge_amount')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('deposit_amount')->default(0);
            $table->integer('total_amount')->default(0);
            $table->integer('paid_amount')->default(0);
            $table->integer('remaining_amount')->default(0);
            $table->integer('change_amount')->default(0);
            $table->enum('payment_method', PaymentMethod::values())->nullable();
            $table->enum('payment_status', InvoicePaymentStatus::values())
                ->default(InvoicePaymentStatus::UNPAID);
            $table->dateTime('issued_at');
            $table->dateTime('paid_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('created_by_employee')
                ->references('user_name')
                ->on('users');
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
