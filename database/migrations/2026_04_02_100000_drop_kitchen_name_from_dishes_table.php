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
        if (! Schema::hasColumn('dishes', 'kitchen_name')) {
            return;
        }

        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('kitchen_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dishes', 'kitchen_name')) {
            return;
        }

        Schema::table('dishes', function (Blueprint $table) {
            $table->string('kitchen_name', 100)->nullable()->after('unit');
        });
    }
};
