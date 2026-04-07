<?php

namespace App\Common\Constants;

use App\Common\Constants\Concerns\HasValues;

final class InvoicePaymentStatus
{
    use HasValues;

    public const UNPAID = 'UNPAID';
    public const PARTIAL = 'PARTIAL';
    public const PAID = 'PAID';
}
