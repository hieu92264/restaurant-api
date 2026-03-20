<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class SelectionType
{
    use HasValues;

    public const SINGLE = 'SINGLE';
    public const MULTIPLE = 'MULTIPLE';
}
