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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_type_id')->constrained('item_types');
            $table->foreignId('category_id')->constrained('menu_categories');
            $table->foreignId('cooking_method_id')->nullable()->constrained('cooking_methods')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('base_unit', 30)->nullable();
            $table->text('remark')->nullable();
            $table->string('image_url')->nullable();
            $table->string('kitchen_print_name', 150)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_item')->default(false);
            $table->integer('sort_order')->default(0);
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->enum('status', MenuItemStatus::values())
                ->default(MenuItemStatus::ACTIVE);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
