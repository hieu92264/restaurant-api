<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends BaseModel
{
    protected $fillable = [
        'category_id',
        'slug',
        'name',
        'description',
        'price',
        'original_price',
        'cost_price',
        'image_url',
        'unit',
        'is_featured',
        'published_at',
        'status',
        'available_from',
        'available_to',
        'sort_order',
        'options_json',
        'tags_json',
        'is_active',
    ];

    protected $appends = [
        'is_new',
        'discounted_price'
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'published_at' => 'date',
            'available_from' => 'string',
            'available_to' => 'string',
            'sort_order' => 'integer',
            'options_json' => 'array',
            'tags_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected function isNew(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->published_at !== null
                && $this->published_at->greaterThanOrEqualTo(now()->startOfDay()->subDays(30))
        );
    }

    protected function discountedPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $activeDiscounts = $this->discounts()
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', now());
                    })
                    ->get();

                $maxDiscount = $activeDiscounts->max(function ($discount) {
                    return $discount->discount_type === 'percentage'
                        ? ($this->price * ($discount->discount_value / 100))
                        : $discount->discount_value;
                });

                return max($this->price - $maxDiscount, 0);
            }
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function comboDishes(): HasMany
    {
        return $this->hasMany(ComboDish::class, 'dish_id');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_dishes', 'dish_id', 'combo_id')
            ->withPivot(['quantity', 'sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'dish_discount');
    }
}
