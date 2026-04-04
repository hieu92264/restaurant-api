<?php

use App\Common\Constants\ComboTag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('slug', 30)->unique();
            $table->string('name', 150);

            $table->text('remark')->nullable();

            $table->integer('combo_price');

            $table->integer('max_use_times')->default(0);

            $table->enum('tag', ComboTag::values())->nullable();
            $table->json('days_in_week')->nullable();

            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
