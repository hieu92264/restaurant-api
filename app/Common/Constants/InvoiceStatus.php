<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class InvoiceStatus
{
    use HasValues;

    public const PENDING = 'PENDING';
    public const PARTIALLY_PAID = 'PARTIALLY_PAID';
    public const PAID = 'PAID';
    public const CANCELLED = 'CANCELLED';
    public const FAILED = 'FAILED';
}
