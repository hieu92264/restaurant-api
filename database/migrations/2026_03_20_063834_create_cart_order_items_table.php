<?php

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\OrderLineStatus;
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
        Schema::create('cart_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_order_id')->constrained('cart_orders');
            $table->foreignId('combo_id')->nullable()->constrained('combos')->nullOnDelete();
            $table->string('item_name_snapshot', 150);
            $table->string('variant_name_snapshot', 150)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('base_unit_price', 12, 2);
            $table->decimal('option_total_price', 12, 2)->default(0);
            $table->decimal('unit_final_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->string('item_note', 255)->nullable();
            $table->enum('line_status', OrderLineStatus::values())
                ->default(OrderLineStatus::ACTIVE);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_order_items');
    }
};
