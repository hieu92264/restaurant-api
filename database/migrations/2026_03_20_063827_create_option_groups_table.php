<?php

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\SelectionType;
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
        Schema::create('option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->enum('selection_type', SelectionType::values())
                ->default(SelectionType::SINGLE);
            $table->boolean('is_required')->default(false);
            $table->integer('min_select')->default(0);
            $table->integer('max_select')->nullable();
            $table->integer('display_order')->default(0);
            $table->char('is_active', 1)->default(ActiveStatus::YES);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_groups');
    }
};
