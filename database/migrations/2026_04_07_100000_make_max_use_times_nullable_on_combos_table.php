<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('combos') || ! Schema::hasColumn('combos', 'max_use_times')) {
            return;
        }

        Schema::table('combos', function (Blueprint $table) {
            $table->integer('max_use_times')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('combos') || ! Schema::hasColumn('combos', 'max_use_times')) {
            return;
        }

        DB::table('combos')
            ->whereNull('max_use_times')
            ->update(['max_use_times' => 0]);

        Schema::table('combos', function (Blueprint $table) {
            $table->integer('max_use_times')->default(0)->nullable(false)->change();
        });
    }
};
