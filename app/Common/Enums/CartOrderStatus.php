<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class CartOrderStatus
{
    use HasValues;

    public const OPEN = 'OPEN';
    public const LOCKED_FOR_PAYMENT = 'LOCKED_FOR_PAYMENT';
    public const CANCELLED = 'CANCELLED';
    public const CONVERTED_TO_INVOICE = 'CONVERTED_TO_INVOICE';
}
