<?php

namespace App\Common\Enums;

use App\Common\Enums\Concerns\HasValues;

final class ActiveStatus
{
    use HasValues;

    public const YES = 'Y';
    public const NO = 'N';
}
