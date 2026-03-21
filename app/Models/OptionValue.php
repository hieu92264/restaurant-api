<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $option_group_id
 * @property string $value_code
 * @property string $value_name
 * @property numeric $price_delta
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItemOption> $cartOrderItemOptions
 * @property-read int|null $cart_order_item_options_count
 * @property-read \App\Models\OptionGroup $optionGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereOptionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue wherePriceDelta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereValueCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionValue whereValueName($value)
 * @mixin \Eloquent
 */
class OptionValue extends BaseModel
{
    protected $fillable = [
        'option_group_id',
        'value_code',
        'value_name',
        'price_delta',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'option_group_id' => 'integer',
            'price_delta' => 'decimal:2',
            'is_active' => 'string',
        ];
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }

    public function cartOrderItemOptions(): HasMany
    {
        return $this->hasMany(CartOrderItemOption::class, 'option_value_id');
    }
}
