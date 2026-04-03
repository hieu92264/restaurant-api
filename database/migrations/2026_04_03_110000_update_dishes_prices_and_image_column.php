<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dishes')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (Schema::hasColumn('dishes', 'image_url') && !Schema::hasColumn('dishes', 'image')) {
            Schema::table('dishes', function (Blueprint $table) {
                $table->renameColumn('image_url', 'image');
            });
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE dishes MODIFY price INTEGER NOT NULL');
            DB::statement('ALTER TABLE dishes MODIFY original_price INTEGER NULL');
            DB::statement('ALTER TABLE dishes MODIFY cost_price INTEGER NULL');
            DB::statement('ALTER TABLE dishes MODIFY image TEXT NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dishes')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (Schema::hasColumn('dishes', 'image') && !Schema::hasColumn('dishes', 'image_url')) {
            Schema::table('dishes', function (Blueprint $table) {
                $table->renameColumn('image', 'image_url');
            });
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE dishes MODIFY price DECIMAL(12,2) NOT NULL');
            DB::statement('ALTER TABLE dishes MODIFY original_price DECIMAL(12,2) NULL');
            DB::statement('ALTER TABLE dishes MODIFY cost_price DECIMAL(12,2) NULL');
            DB::statement('ALTER TABLE dishes MODIFY image_url VARCHAR(255) NULL');
        }
    }
};
