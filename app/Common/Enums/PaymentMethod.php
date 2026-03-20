<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class PaymentMethod
{
    use HasValues;

    public const CASH = 'CASH';
    public const BANK_QR = 'BANK_QR';
    public const CARD = 'CARD';
    public const EWALLET = 'EWALLET';
}
