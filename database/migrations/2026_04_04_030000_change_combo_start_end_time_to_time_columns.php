<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('combos') || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE combos MODIFY start_time TIME NULL');
            DB::statement('ALTER TABLE combos MODIFY end_time TIME NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('combos') || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE combos MODIFY start_time DATETIME NULL');
            DB::statement('ALTER TABLE combos MODIFY end_time DATETIME NULL');
        }
    }
};
