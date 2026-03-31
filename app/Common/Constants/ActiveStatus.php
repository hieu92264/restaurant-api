<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class ActiveStatus
{
    use HasValues;

    public const YES = true;
    public const NO = false;
}
