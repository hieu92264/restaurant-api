<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\SelectionType;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $selection_type
 * @property bool $is_required
 * @property int $min_select
 * @property int|null $max_select
 * @property int $display_order
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItemOption> $cartOrderItemOptions
 * @property-read int|null $cart_order_item_options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OptionValue> $values
 * @property-read int|null $values_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItemVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereMaxSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereMinSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereSelectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OptionGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OptionGroup extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'selection_type',
        'is_required',
        'min_select',
        'max_select',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selection_type' => 'string',
            'is_required' => 'boolean',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'string',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class, 'option_group_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItemVariant::class,
            'variant_option_groups',
            'option_group_id',
            'variant_id'
        )->withPivot(['display_order', 'is_active']);
    }

    public function cartOrderItemOptions(): HasMany
    {
        return $this->hasMany(CartOrderItemOption::class, 'option_group_id');
    }
}
