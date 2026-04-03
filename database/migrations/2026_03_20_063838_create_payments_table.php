<?php

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\PaymentMethod;
use App\Common\Constants\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->enum('method', PaymentMethod::values());
            $table->string('transaction_code', 100)->nullable();
            $table->text('qr_content')->nullable();
            $table->integer('requested_amount')->default(0);
            $table->integer('paid_amount')->default(0);
            $table->dateTime('paid_time')->nullable();
            $table->enum('status', PaymentStatus::values())
                ->default(PaymentStatus::PENDING);
            $table->foreignId('confirmed_by_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
