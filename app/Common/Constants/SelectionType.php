<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class SelectionType
{
    use HasValues;

    public const SINGLE = 'SINGLE';
    public const MULTIPLE = 'MULTIPLE';
}
