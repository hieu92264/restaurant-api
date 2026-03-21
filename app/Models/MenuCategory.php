<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property int $item_type_id
 * @property string $code
 * @property string $name
 * @property int $sort_order
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuCategory> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\ItemType $itemType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItem> $menuItems
 * @property-read int|null $menu_items_count
 * @property-read MenuCategory|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereItemTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MenuCategory extends BaseModel
{
    protected $fillable = [
        'parent_id',
        'item_type_id',
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'item_type_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'string',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }
}
