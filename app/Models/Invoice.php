<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $no
 * @property int $cart_order_id
 * @property int $session_id
 * @property int $table_id
 * @property int $created_by_employee_id
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property numeric $subtotal_amount
 * @property numeric $discount_amount
 * @property numeric $service_charge_amount
 * @property numeric $tax_amount
 * @property numeric $total_amount
 * @property numeric $paid_amount
 * @property numeric $change_amount
 * @property string $invoice_status
 * @property \Illuminate\Support\Carbon $issued_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $note
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CartOrder $cartOrder
 * @property-read \App\Models\User $createdByEmployee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\TableSession $session
 * @property-read \App\Models\RestaurantTable $table
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCartOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereChangeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedByEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereServiceChargeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Invoice extends BaseModel
{
    protected $fillable = [
        'no',
        'cart_order_id',
        'session_id',
        'table_id',
        'created_by_employee_id',
        'customer_name',
        'customer_phone',
        'subtotal_amount',
        'discount_amount',
        'service_charge_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'invoice_status',
        'issued_at',
        'paid_at',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cart_order_id' => 'integer',
            'session_id' => 'integer',
            'table_id' => 'integer',
            'created_by_employee_id' => 'integer',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'invoice_status' => 'string',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function cartOrder(): BelongsTo
    {
        return $this->belongsTo(CartOrder::class, 'cart_order_id');
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
        return $this->belongsTo(User::class, 'created_by_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}
