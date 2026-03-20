<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class RestaurantTableStatus
{
    use HasValues;

    public const AVAILABLE = 'AVAILABLE';
    public const OCCUPIED = 'OCCUPIED';
    public const RESERVED = 'RESERVED';
    public const CLEANING = 'CLEANING';
    public const DISABLED = 'DISABLED';
}
