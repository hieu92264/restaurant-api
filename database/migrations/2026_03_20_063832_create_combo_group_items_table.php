<?php

use App\Common\Enums\ActiveStatus;
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
        Schema::create('combo_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_group_id')->constrained('combo_groups');
            $table->foreignId('menu_item_id')->constrained('menu_items');
            $table->foreignId('variant_id')->nullable()->constrained('menu_item_variants')->nullOnDelete();
            $table->decimal('extra_price', 12, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_group_items');
    }
};
