<?php

namespace App\Common\Constants\Concerns;

use ReflectionClass;

trait HasValues
{
    public static function values(): array
    {
        return array_values((new ReflectionClass(static::class))->getConstants());
    }
}
