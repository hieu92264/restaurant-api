<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class MenuItemStatus
{
    use HasValues;

    public const ACTIVE = 'ACTIVE';
    public const INACTIVE = 'INACTIVE';
    public const OUT_OF_STOCK = 'OUT_OF_STOCK';
}
