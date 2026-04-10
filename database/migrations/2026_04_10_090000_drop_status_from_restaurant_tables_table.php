<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('restaurant_tables', 'status')) {
            return;
        }

        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurant_tables', 'status')) {
            return;
        }

        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->string('status', 20)->default('AVAILABLE')->after('capacity');
        });
    }
};
