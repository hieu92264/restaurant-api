<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartOrderItemOption extends BaseModel
{
    public $timestamps = false;

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
