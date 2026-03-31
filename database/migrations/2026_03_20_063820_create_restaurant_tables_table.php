<?php

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\RestaurantTableStatus;
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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('table_areas')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->integer('capacity');
            $table->enum('status', RestaurantTableStatus::values())
                ->default(RestaurantTableStatus::AVAILABLE);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
