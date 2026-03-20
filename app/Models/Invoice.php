<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\InvoiceStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'is_active' => 'string',
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
