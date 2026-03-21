<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $variant_id
 * @property int $option_group_id
 * @property int $display_order
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OptionGroup $optionGroup
 * @property-read \App\Models\MenuItemVariant $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereOptionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VariantOptionGroup whereVariantId($value)
 * @mixin \Eloquent
 */
class VariantOptionGroup extends BaseModel
{
    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'variant_id',
        'option_group_id',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variant_id' => 'integer',
            'option_group_id' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuItemVariant::class, 'variant_id');
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }
}
