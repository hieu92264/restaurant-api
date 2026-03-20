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
        Schema::create('variant_option_groups', function (Blueprint $table) {
            $table->foreignId('variant_id')->constrained('menu_item_variants');
            $table->foreignId('option_group_id')->constrained('option_groups');
            $table->integer('display_order')->default(0);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->primary(['variant_id', 'option_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_option_groups');
    }
};
