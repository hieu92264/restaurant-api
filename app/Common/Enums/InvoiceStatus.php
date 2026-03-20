<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class InvoiceStatus
{
    use HasValues;

    public const PENDING = 'PENDING';
    public const PARTIALLY_PAID = 'PARTIALLY_PAID';
    public const PAID = 'PAID';
    public const CANCELLED = 'CANCELLED';
    public const FAILED = 'FAILED';
}
