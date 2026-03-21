<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $combo_group_id
 * @property int $menu_item_id
 * @property int|null $variant_id
 * @property numeric $extra_price
 * @property bool $is_default
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ComboGroup $comboGroup
 * @property-read \App\Models\MenuItem $menuItem
 * @property-read \App\Models\MenuItemVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereComboGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroupItem whereVariantId($value)
 * @mixin \Eloquent
 */
class ComboGroupItem extends BaseModel
{
    protected $fillable = [
        'combo_group_id',
        'menu_item_id',
        'variant_id',
        'extra_price',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'combo_group_id' => 'integer',
            'menu_item_id' => 'integer',
            'variant_id' => 'integer',
            'extra_price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'string',
        ];
    }

    public function comboGroup(): BelongsTo
    {
        return $this->belongsTo(ComboGroup::class, 'combo_group_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuItemVariant::class, 'variant_id');
    }
}
