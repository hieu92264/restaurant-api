<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\CartOrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CartOrder extends BaseModel
{
    protected $fillable = [
        'session_id',
        'table_id',
        'order_no',
        'created_by_employee_id',
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
            'created_by_employee_id' => 'integer',
            'status' => 'string',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_active' => 'string',
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
        return $this->belongsTo(User::class, 'created_by_employee_id');
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
