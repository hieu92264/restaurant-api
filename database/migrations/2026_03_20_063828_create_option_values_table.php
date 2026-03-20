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
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained('option_groups');
            $table->string('value_code', 30);
            $table->string('value_name', 100);
            $table->decimal('price_delta', 12, 2)->default(0);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->unique(['option_group_id', 'value_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};
