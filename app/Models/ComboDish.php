<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $combo_id
 * @property int $dish_id
 * @property int $quantity
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Combo $combo
 * @property-read \App\Models\Dish $dish
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereComboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereDishId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboDish whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ComboDish extends BaseModel
{
    protected $fillable = [
        'combo_id',
        'dish_id',
        'quantity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'combo_id' => 'integer',
            'dish_id' => 'integer',
            'quantity' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }
}
