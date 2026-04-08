<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property bool $is_active
 * @property string $reservation_code
 * @property string $customer_name
 * @property string|null $customer_phone
 * @property int $guest_count
 * @property \Illuminate\Support\Carbon $reservation_time
 * @property string|null $remark
 * @property string $status
 * @property int $deposit_amount
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $hold_start_time
 * @property \Illuminate\Support\Carbon|null $hold_end_time
 * @property string|null $created_by_employee
 * @property string|null $confirmed_by_employee
 * @property string|null $cancelled_by_employee
 * @property string|null $table_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $cancelledByEmployee
 * @property-read \App\Models\User|null $confirmedByEmployee
 * @property-read \App\Models\User|null $createdByEmployee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TableSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read \App\Models\RestaurantTable|null $table
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCancelledByEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereConfirmedByEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCreatedByEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereDepositAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereGuestCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereHoldEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereHoldStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereReservationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereReservationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereTableCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Reservation extends BaseModel
{
    protected $fillable = [
        'is_active',
        'reservation_code',
        'customer_name',
        'customer_phone',
        'guest_count',
        'reservation_time',
        'remark',
        'status',
        'deposit_amount',
        'confirmed_at',
        'cancelled_at',
        'hold_start_time',
        'hold_end_time',
        'created_by_employee',
        'confirmed_by_employee',
        'cancelled_by_employee',
        'table_code',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'guest_count' => 'integer',
            'reservation_time' => 'datetime',
            'deposit_amount' => 'integer',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'hold_start_time' => 'datetime',
            'hold_end_time' => 'datetime',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function createdByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_employee', 'user_name');
    }

    public function confirmedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_employee', 'user_name');
    }

    public function cancelledByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_employee', 'user_name');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_code', 'slug');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class, 'reservation_code', 'reservation_code');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'reservation_code', 'reservation_code');
    }
}
