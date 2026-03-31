<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_order_id
 * @property int|null $combo_id
 * @property string $item_name_snapshot
 * @property string|null $variant_name_snapshot
 * @property numeric $quantity
 * @property numeric $base_unit_price
 * @property numeric $option_total_price
 * @property numeric $unit_final_price
 * @property numeric $line_total
 * @property string|null $item_note
 * @property string $line_status
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CartOrder $cartOrder
 * @property-read \App\Models\Combo|null $combo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereBaseUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereCartOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereComboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereItemNameSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereItemNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereLineStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereOptionTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereUnitFinalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItem whereVariantNameSnapshot($value)
 * @mixin \Eloquent
 */
class CartOrderItem extends BaseModel
{
    protected $fillable = [
        'cart_order_id',
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
            'combo_id' => 'integer',
            'quantity' => 'decimal:2',
            'base_unit_price' => 'decimal:2',
            'option_total_price' => 'decimal:2',
            'unit_final_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function cartOrder(): BelongsTo
    {
        return $this->belongsTo(CartOrder::class, 'cart_order_id');
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
}
