<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('combos') || Schema::hasColumn('combos', 'combo_image')) {
            return;
        }

        Schema::table('combos', function (Blueprint $table) {
            $table->json('combo_image')->nullable()->after('remark');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('combos') || ! Schema::hasColumn('combos', 'combo_image')) {
            return;
        }

        Schema::table('combos', function (Blueprint $table) {
            $table->dropColumn('combo_image');
        });
    }
};
