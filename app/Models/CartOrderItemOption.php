<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_order_item_id
 * @property int $option_group_id
 * @property int $option_value_id
 * @property string $option_group_name_snapshot
 * @property string $option_value_name_snapshot
 * @property numeric $price_delta_snapshot
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CartOrderItem $cartOrderItem
 * @property-read \App\Models\OptionGroup $optionGroup
 * @property-read \App\Models\OptionValue $optionValue
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereCartOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereOptionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereOptionGroupNameSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereOptionValueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereOptionValueNameSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption wherePriceDeltaSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartOrderItemOption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CartOrderItemOption extends BaseModel
{
    protected $fillable = [
        'cart_order_item_id',
        'option_group_id',
        'option_value_id',
        'option_group_name_snapshot',
        'option_value_name_snapshot',
        'price_delta_snapshot',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cart_order_item_id' => 'integer',
            'option_group_id' => 'integer',
            'option_value_id' => 'integer',
            'price_delta_snapshot' => 'decimal:2',
            'is_active' => 'string',
        ];
    }

    public function cartOrderItem(): BelongsTo
    {
        return $this->belongsTo(CartOrderItem::class, 'cart_order_item_id');
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class, 'option_value_id');
    }
}
