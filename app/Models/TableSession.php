<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $table_id
 * @property int $opened_by_employee_id
 * @property int|null $closed_by_employee_id
 * @property int|null $guest_count
 * @property string $status
 * @property \Illuminate\Support\Carbon $opened_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property string|null $remark
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrder> $cartOrders
 * @property-read int|null $cart_orders_count
 * @property-read \App\Models\User|null $closedByEmployee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\User $openedByEmployee
 * @property-read \App\Models\RestaurantTable $table
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereClosedByEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereGuestCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereOpenedByEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableSession whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TableSession extends BaseModel
{
    protected $fillable = [
        'table_id',
        'opened_by_employee_id',
        'closed_by_employee_id',
        'guest_count',
        'status',
        'opened_at',
        'closed_at',
        'remark',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'table_id' => 'integer',
            'opened_by_employee_id' => 'integer',
            'closed_by_employee_id' => 'integer',
            'guest_count' => 'integer',
            'status' => 'string',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function openedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_employee_id');
    }

    public function closedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_employee_id');
    }

    public function cartOrders(): HasMany
    {
        return $this->hasMany(CartOrder::class, 'session_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'session_id');
    }
}
