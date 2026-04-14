<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CURRENT_VALUES = [
        'CASH',
        'TRANSFER',
        'BANK_QR',
        'CARD',
        'EWALLET',
    ];

    private const LEGACY_VALUES = [
        'CASH',
        'BANK_QR',
        'CARD',
        'EWALLET',
    ];

    public function up(): void
    {
        if (! $this->shouldModifyPaymentMethodEnum()) {
            return;
        }

        $this->modifyPaymentMethodEnum(self::CURRENT_VALUES);
    }

    public function down(): void
    {
        if (! $this->shouldModifyPaymentMethodEnum()) {
            return;
        }

        DB::table('invoices')
            ->where('payment_method', 'TRANSFER')
            ->update([
                'payment_method' => 'BANK_QR',
            ]);

        $this->modifyPaymentMethodEnum(self::LEGACY_VALUES);
    }

    private function shouldModifyPaymentMethodEnum(): bool
    {
        return DB::getDriverName() === 'mysql'
            && Schema::hasTable('invoices')
            && Schema::hasColumn('invoices', 'payment_method');
    }

    private function modifyPaymentMethodEnum(array $values): void
    {
        $quotedValues = implode(',', array_map(
            static fn (string $value): string => "'" . $value . "'",
            $values
        ));

        DB::statement("ALTER TABLE invoices MODIFY payment_method ENUM($quotedValues) NULL");
    }
};
