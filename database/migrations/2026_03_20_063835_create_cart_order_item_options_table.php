<?php

use App\Common\Constants\ActiveStatus;
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
        Schema::create('cart_order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_order_item_id')->constrained('cart_order_items');
            $table->foreignId('option_group_id')->constrained('option_groups');
            $table->foreignId('option_value_id')->constrained('option_values');
            $table->string('option_group_name_snapshot', 100);
            $table->string('option_value_name_snapshot', 100);
            $table->decimal('price_delta_snapshot', 12, 2)->default(0);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_order_item_options');
    }
};
