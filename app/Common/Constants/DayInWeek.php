<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class DayInWeek
{
    use HasValues;

    public const MONDAY = 'T2';
    public const TUESDAY = 'T3';
    public const WEDNESDAY = 'T4';
    public const THURSDAY = 'T5';
    public const FRIDAY = 'T6';
    public const SATURDAY = 'T7';
    public const SUNDAY = 'CN';
}
