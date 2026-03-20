<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class PaymentStatus
{
    use HasValues;

    public const PENDING = 'PENDING';
    public const SUCCESS = 'SUCCESS';
    public const FAILED = 'FAILED';
    public const CANCELLED = 'CANCELLED';
    public const REFUNDED = 'REFUNDED';
}
