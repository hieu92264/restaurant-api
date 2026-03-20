<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionValue extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'option_group_id',
        'value_code',
        'value_name',
        'price_delta',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'option_group_id' => 'integer',
            'price_delta' => 'decimal:2',
            'is_active' => 'string',
        ];
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }

    public function cartOrderItemOptions(): HasMany
    {
        return $this->hasMany(CartOrderItemOption::class, 'option_value_id');
    }
}
