<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class ReservationStatus
{
    use HasValues;

    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const COMPLETED = 'completed';
    const CANCELED = 'canceled';
}
