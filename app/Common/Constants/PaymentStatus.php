<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class PaymentStatus
{
    use HasValues;

    public const PENDING = 'PENDING';
    public const SUCCESS = 'SUCCESS';
    public const FAILED = 'FAILED';
    public const CANCELLED = 'CANCELLED';
    public const REFUNDED = 'REFUNDED';
}
