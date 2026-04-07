<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('combos')) {
            return;
        }

        if (Schema::hasColumn('combos', 'combo_price') && ! Schema::hasColumn('combos', 'discount_price')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->renameColumn('combo_price', 'discount_price');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('combos')) {
            return;
        }

        if (Schema::hasColumn('combos', 'discount_price') && ! Schema::hasColumn('combos', 'combo_price')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->renameColumn('discount_price', 'combo_price');
            });
        }
    }
};
