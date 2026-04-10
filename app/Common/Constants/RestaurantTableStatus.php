<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class RestaurantTableStatus
{
    use HasValues;

    public const AVAILABLE = 'AVAILABLE';
    public const OCCUPIED = 'OCCUPIED';
    public const RESERVED = 'RESERVED';
}
