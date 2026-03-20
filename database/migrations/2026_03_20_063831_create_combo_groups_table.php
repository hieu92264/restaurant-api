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
        Schema::create('combo_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos');
            $table->string('group_name', 100);
            $table->integer('min_select')->default(1);
            $table->integer('max_select')->default(1);
            $table->integer('display_order')->default(0);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_groups');
    }
};
