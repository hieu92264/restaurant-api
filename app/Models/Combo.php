<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $remark
 * @property numeric $base_price
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $cartOrderItems
 * @property-read int|null $cart_order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboDish> $comboDishes
 * @property-read int|null $combo_dishes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dish> $dishes
 * @property-read int|null $dishes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $invoiceItems
 * @property-read int|null $invoice_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Combo extends BaseModel
{
    protected $fillable = [
        'slug',
        'name',
        'remark',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function comboDishes(): HasMany
    {
        return $this->hasMany(ComboDish::class, 'combo_id');
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'combo_dishes', 'combo_id', 'dish_id')
            ->withPivot(['quantity', 'sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'combo_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'combo_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
