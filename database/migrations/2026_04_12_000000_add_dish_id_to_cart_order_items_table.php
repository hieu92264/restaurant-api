<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_order_items', function (Blueprint $table) {
            $table->foreignId('dish_id')->nullable()->constrained('dishes')->nullOnDelete();
        });

        $dishIdsByName = DB::table('dishes')->pluck('id', 'name');

        DB::table('cart_order_items')
            ->select(['id', 'item_name_snapshot'])
            ->whereNull('combo_id')
            ->whereNull('dish_id')
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($dishIdsByName): void {
                foreach ($items as $item) {
                    $dishId = $dishIdsByName[$item->item_name_snapshot] ?? null;

                    if ($dishId === null) {
                        continue;
                    }

                    DB::table('cart_order_items')
                        ->where('id', $item->id)
                        ->update(['dish_id' => $dishId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dish_id');
        });
    }
};
