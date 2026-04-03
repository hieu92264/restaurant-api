<?php

use App\Common\Constants\ActiveStatus;
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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('combo_id')->nullable()->constrained('combos')->nullOnDelete();
            $table->string('item_name_snapshot', 150);
            $table->string('variant_name_snapshot', 150)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->integer('base_unit_price');
            $table->integer('option_total_price')->default(0);
            $table->integer('unit_final_price');
            $table->integer('line_total');
            $table->string('item_note', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
