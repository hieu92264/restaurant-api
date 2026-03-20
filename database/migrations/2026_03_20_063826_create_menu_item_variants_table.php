<?php

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\MenuItemStatus;
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
        Schema::create('menu_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items');
            $table->string('sku_code', 40)->unique();
            $table->string('name', 150);
            $table->string('size_code', 20)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->enum('status', MenuItemStatus::values())
                ->default(MenuItemStatus::ACTIVE);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_variants');
    }
};
