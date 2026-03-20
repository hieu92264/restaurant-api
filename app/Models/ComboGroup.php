<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
