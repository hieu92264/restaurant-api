<?php

use App\Common\Constants\CartOrderStatus;
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
            $table->string('created_by_employee');
            $table->enum('status', CartOrderStatus::values())
                ->default(CartOrderStatus::OPEN);
            $table->integer('subtotal_amount')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('service_charge_amount')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_amount')->default(0);
            $table->string('remark', 255)->nullable();
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
        Schema::dropIfExists('cart_orders');
    }
};
