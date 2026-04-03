<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property string $method
 * @property string|null $transaction_code
 * @property string|null $qr_content
 * @property numeric $requested_amount
 * @property numeric $paid_amount
 * @property \Illuminate\Support\Carbon|null $paid_time
 * @property string $status
 * @property int|null $confirmed_by_employee_id
 * @property string|null $note
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $confirmedByEmployee
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereConfirmedByEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereQrContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRequestedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends BaseModel
{
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
            'requested_amount' => 'integer',
            'paid_amount' => 'integer',
            'paid_time' => 'datetime',
            'status' => 'string',
            'confirmed_by_employee_id' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
