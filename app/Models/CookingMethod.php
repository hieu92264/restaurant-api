<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItem> $menuItems
 * @property-read int|null $menu_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CookingMethod extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'string',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'cooking_method_id');
    }
}
