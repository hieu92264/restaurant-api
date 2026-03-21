<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $combo_id
 * @property string $group_name
 * @property int $min_select
 * @property int $max_select
 * @property int $display_order
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Combo $combo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboGroupItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereComboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereMaxSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereMinSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComboGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ComboGroup extends BaseModel
{
    protected $fillable = [
        'combo_id',
        'group_name',
        'min_select',
        'max_select',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'combo_id' => 'integer',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'string',
        ];
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ComboGroupItem::class, 'combo_group_id');
    }
}
