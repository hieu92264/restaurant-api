<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $session_id
 * @property int $table_id
 * @property string $order_no
 * @property string $created_by_employee
 * @property string $status
 * @property numeric $subtotal_amount
 * @property numeric $discount_amount
 * @property numeric $service_charge_amount
 * @property numeric $tax_amount
 * @property numeric $total_amount
 * @property string|null $remark
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdByEmployee
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\TableSession $session
 * @property-read \App\Models\RestaurantTable $table
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereCreatedByEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereServiceChargeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereSubtotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CartOrder extends BaseModel
{
    protected $fillable = [
        'session_id',
        'table_id',
        'order_no',
        'created_by_employee',
        'status',
        'subtotal_amount',
        'discount_amount',
        'service_charge_amount',
        'tax_amount',
        'total_amount',
        'remark',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'session_id' => 'integer',
            'table_id' => 'integer',
            'created_by_employee' => 'string',
            'status' => 'string',
            'subtotal_amount' => 'integer',
            'discount_amount' => 'integer',
            'service_charge_amount' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function createdByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_employee', 'user_name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'cart_order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'cart_order_id');
    }
}
