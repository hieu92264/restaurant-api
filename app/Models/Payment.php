<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\PaymentMethod;
use App\Common\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'method',
        'transaction_code',
        'qr_content',
        'requested_amount',
        'paid_amount',
        'paid_time',
        'status',
        'confirmed_by_employee_id',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'method' => 'string',
            'requested_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_time' => 'datetime',
            'status' => 'string',
            'confirmed_by_employee_id' => 'integer',
            'is_active' => 'string',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function confirmedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_employee_id');
    }
}
