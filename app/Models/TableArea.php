<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RestaurantTable> $restaurantTables
 * @property-read int|null $restaurant_tables_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TableArea extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function restaurantTables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'area_id');
    }
}
