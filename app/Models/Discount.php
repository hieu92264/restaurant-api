<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    protected $fillable = [
        'is_active',
        'slug',
        'name',
        'description',
        'discount_type',
        'scope',
        'starts_at',
        'ends_at',
        'quantity',
        'max_use_times',
        'discount_value',
        'min_order_value',
        'sort_order',
    ];

    protected $appends = [
        'is_valid',
    ];

    protected function isValid(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = now();
                return $this->is_active
                    && ($this->starts_at === null || $this->starts_at->lessThanOrEqualTo($now))
                    && ($this->ends_at === null || $this->ends_at->greaterThanOrEqualTo($now));
            }
        );
    }

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount_value' => 'integer',
        'min_order_value' => 'integer',
    ];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_discount');
    }
}
