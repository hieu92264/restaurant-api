<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\OrderLineStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartOrderItem extends BaseModel
{
    protected $fillable = [
        'cart_order_id',
        'menu_item_id',
        'variant_id',
        'combo_id',
        'item_name_snapshot',
        'variant_name_snapshot',
        'quantity',
        'base_unit_price',
        'option_total_price',
        'unit_final_price',
        'line_total',
        'item_note',
        'line_status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cart_order_id' => 'integer',
            'menu_item_id' => 'integer',
            'variant_id' => 'integer',
            'combo_id' => 'integer',
            'quantity' => 'decimal:2',
            'base_unit_price' => 'decimal:2',
            'option_total_price' => 'decimal:2',
            'unit_final_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active' => 'string',
        ];
    }

    public function cartOrder(): BelongsTo
    {
        return $this->belongsTo(CartOrder::class, 'cart_order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuItemVariant::class, 'variant_id');
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CartOrderItemOption::class, 'cart_order_item_id');
    }
}
