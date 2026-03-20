<?php

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\CartOrderStatus;
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
        Schema::create('cart_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('table_sessions');
            $table->foreignId('table_id')->constrained('restaurant_tables');
            $table->string('order_no', 30)->unique();
            $table->foreignId('created_by_employee_id')->constrained('users');
            $table->enum('status', CartOrderStatus::values())
                ->default(CartOrderStatus::OPEN);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('service_charge_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('remark', 255)->nullable();
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_orders');
    }
};
