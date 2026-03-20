<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
