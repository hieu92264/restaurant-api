<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('combo_dishes') || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE combo_dishes MODIFY quantity INTEGER NOT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('combo_dishes') || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE combo_dishes MODIFY quantity DECIMAL(10,2) NOT NULL DEFAULT 1');
        }
    }
};
