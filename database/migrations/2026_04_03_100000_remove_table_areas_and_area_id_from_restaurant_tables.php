<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('restaurant_tables', 'area_id')) {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->dropConstrainedForeignId('area_id');
            });
        }

        Schema::dropIfExists('table_areas');
    }

    public function down(): void
    {
        if (! Schema::hasTable('table_areas')) {
            Schema::create('table_areas', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 20)->unique();
                $table->string('name', 100);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('restaurant_tables', 'area_id')) {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->foreignId('area_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('table_areas')
                    ->nullOnDelete();
            });
        }
    }
};
