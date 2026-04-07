<?php

use App\Common\Constants\ReservationStatus;
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            $table->string('reservation_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->integer('guest_count');
            $table->dateTime('reservation_time');
            $table->text('remark')->nullable();
            $table->enum('status', ReservationStatus::values())->default(ReservationStatus::PENDING);
            $table->integer('deposit_amount')->default(0);

            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('hold_start_time')->nullable();
            $table->dateTime('hold_end_time')->nullable();

            $table->string('created_by_employee')->nullable();
            $table->string('confirmed_by_employee')->nullable();
            $table->string('cancelled_by_employee')->nullable();
            $table->string('table_code')->nullable();

            $table->foreign('created_by_employee')
                ->references('user_name')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('confirmed_by_employee')
                ->references('user_name')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('cancelled_by_employee')
                ->references('user_name')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('table_code')
                ->references('slug')
                ->on('restaurant_tables')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
