<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class ComboTag
{
    use HasValues;

    public const HOT = 'HOT';
    public const SEASONAL = 'SEASONAL';
    public const FAST = 'FAST';
    public const RELAX = 'RELAX';
}
