<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\SelectionType;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
