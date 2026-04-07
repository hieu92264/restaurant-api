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
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->string('reservation_code')->nullable()->after('remark');
            $table->foreign('reservation_code')
                ->references('reservation_code')
                ->on('reservations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['reservation_code']);
            $table->dropColumn('reservation_code');
        });
    }
};
