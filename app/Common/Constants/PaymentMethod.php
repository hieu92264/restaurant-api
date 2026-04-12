<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class PaymentMethod
{
    use HasValues;

    public const CASH = 'CASH';
    public const TRANSFER = 'TRANSFER';
    public const BANK_QR = 'BANK_QR';
    public const CARD = 'CARD';
    public const EWALLET = 'EWALLET';

    public static function normalize(null|string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match (strtoupper($value)) {
            self::BANK_QR => self::TRANSFER,
            default => strtoupper($value),
        };
    }

    public static function display(null|string $value): ?string
    {
        return self::normalize($value);
    }
}
