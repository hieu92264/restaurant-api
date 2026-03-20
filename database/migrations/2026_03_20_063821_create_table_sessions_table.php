<?php

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\TableSessionStatus;
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
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('restaurant_tables');
            $table->foreignId('opened_by_employee_id')->constrained('users');
            $table->foreignId('closed_by_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('guest_count')->nullable();
            $table->enum('status', TableSessionStatus::values())
                ->default(TableSessionStatus::OPEN);
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->string('remark', 255)->nullable();
            $table->char('is_active', 1)->default(ActiveStatus::YES);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_sessions');
    }
};
