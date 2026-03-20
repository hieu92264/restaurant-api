<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class OrderLineStatus
{
    use HasValues;

    public const ACTIVE = 'ACTIVE';
    public const CANCELLED = 'CANCELLED';
    public const SERVED = 'SERVED';
}
