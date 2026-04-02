<?php

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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('scope', ['dish', 'invoice'])->default('dish');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('max_use_times')->nullable();
            $table->integer('discount_value')->default(0);
            $table->decimal('min_order_value', 8, 2)->nullable();
            $table->decimal('sort_order', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
