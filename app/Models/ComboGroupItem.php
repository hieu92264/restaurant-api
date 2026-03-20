<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
