<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class OrderLineStatus
{
    use HasValues;

    public const ACTIVE = 'ACTIVE';
    public const CANCELLED = 'CANCELLED';
    public const SERVED = 'SERVED';
}
