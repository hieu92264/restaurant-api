<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class TableSessionStatus
{
    use HasValues;

    public const OPEN = 'OPEN';
    public const PAYMENT_PENDING = 'PAYMENT_PENDING';
    public const PAID = 'PAID';
    public const CANCELLED = 'CANCELLED';
    public const CLOSED = 'CLOSED';
}
